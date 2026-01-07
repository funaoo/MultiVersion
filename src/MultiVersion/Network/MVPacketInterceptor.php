<?php
declare(strict_types=1);
namespace MultiVersion\Network;
use MultiVersion\MultiVersion;
use MultiVersion\MultiVersionAPI;
use MultiVersion\Events\PlayerProtocolJoinEvent;
use MultiVersion\Events\PlayerProtocolQuitEvent;
use MultiVersion\Network\Proto\ProtocolRegistry;
use MultiVersion\Network\Proto\Packets\PlayerActionPacketHandler;
use MultiVersion\Network\Session\ProtocolSession;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\LevelChunkPacket;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\player\Player;
use WeakMap;
final class MVPacketInterceptor implements Listener{
    private WeakMap $sessions;
    private ProtocolRegistry $registry;
    private array $errorCounts = [];
    private int $maxErrorsPerPlayer = 10;
    public function __construct(ProtocolRegistry $registry){
        $this->registry = $registry;
        $this->sessions = new WeakMap();
    }
    public function onPacketReceive(DataPacketReceiveEvent $event): void{
        $packet = $event->getPacket();
        $origin = $event->getOrigin();

        if($packet instanceof LoginPacket){
            $protocol = $packet->protocol;

            if(!$this->registry->isSupported($protocol)){
                $origin->disconnect("Unsupported protocol version: {$protocol}");
                return;
            }

            $player = $origin->getPlayer();
            if($player === null){
                return;
            }

            if($protocol === $this->registry->getNativeProtocol()){
                MultiVersion::getInstance()->getLogger()->debug(
                    "Player {$player->getName()} using native protocol {$protocol}"
                );
                return;
            }

            $adapter = $this->registry->getAdapter($protocol);
            $session = new ProtocolSession($player, $protocol, $adapter);
            $this->sessions[$player] = $session;

            MultiVersionAPI::registerPlayer($player, $protocol);

            MultiVersion::getInstance()->getLogger()->info(
                "Player {$player->getName()} joined with protocol {$protocol} ({$adapter->getVersionString()})"
            );

            $ev = new PlayerProtocolJoinEvent($player, $protocol, $adapter->getVersionString());
            $ev->call();
        }

        if($packet instanceof PlayerActionPacket){
            $player = $origin->getPlayer();
            if($player !== null && isset($this->sessions[$player])){
                $session = $this->sessions[$player];
                $translated = PlayerActionPacketHandler::translatePacket($packet, $session->getProtocol());

                if($translated === null){
                    $event->cancel();
                }
            }
        }
    }
    public function onPacketSend(DataPacketSendEvent $event): void{
        $packets = $event->getPackets();

        foreach($event->getTargets() as $target){
            $player = $target->getPlayer();
            if($player === null){
                continue;
            }

            if(!isset($this->sessions[$player])){
                continue;
            }

            $session = $this->sessions[$player];
            if($session->isNativeProtocol()){
                continue;
            }

            $adapter = $session->getAdapter();
            $translatedPackets = [];

            foreach($packets as $packet){
                try{
                    $translated = $this->translatePacket($packet, $adapter, $session);
                    if($translated !== null){
                        $translatedPackets[] = $translated;
                    }
                }catch(\Throwable $e){
                    $this->handleTranslationError($player, $packet, $e);
                    $translatedPackets[] = $packet;
                }
            }

            $packets->clear();
            foreach($translatedPackets as $p){
                $packets->add($p);
            }
        }
    }
    private function translatePacket($packet, $adapter, ProtocolSession $session){
        if($packet instanceof StartGamePacket){
            return $adapter->translateStartGame($packet, $session);
        }

        if($packet instanceof LevelChunkPacket){
            return $adapter->translateLevelChunk($packet, $session);
        }

        if($packet instanceof UpdateBlockPacket){
            return $adapter->translateUpdateBlock($packet, $session);
        }

        if($packet instanceof InventoryContentPacket){
            return $adapter->translateInventoryContent($packet, $session);
        }

        if($packet instanceof InventorySlotPacket){
            return $adapter->translateInventorySlot($packet, $session);
        }

        if($packet instanceof AddActorPacket){
            return $adapter->translateAddActor($packet, $session);
        }

        if($packet instanceof SetActorDataPacket){
            return $adapter->translateSetActorData($packet, $session);
        }

        return $packet;
    }
    private function handleTranslationError(Player $player, $packet, \Throwable $e): void{
        $playerName = $player->getName();
        $packetClass = get_class($packet);

        if(!isset($this->errorCounts[$playerName])){
            $this->errorCounts[$playerName] = [];
        }

        if(!isset($this->errorCounts[$playerName][$packetClass])){
            $this->errorCounts[$playerName][$packetClass] = 0;
        }

        $this->errorCounts[$playerName][$packetClass]++;

        if($this->errorCounts[$playerName][$packetClass] <= 3){
            MultiVersion::getInstance()->getLogger()->warning(
                "Translation error for {$playerName} with packet {$packetClass}: {$e->getMessage()}"
            );
        }

        if($this->errorCounts[$playerName][$packetClass] === $this->maxErrorsPerPlayer){
            MultiVersion::getInstance()->getLogger()->warning(
                "Player {$playerName} has exceeded {$this->maxErrorsPerPlayer} errors for {$packetClass}, suppressing further logs"
            );
        }
    }
    public function onPlayerQuit(PlayerQuitEvent $event): void{
        $player = $event->getPlayer();
        $playerName = $player->getName();

        if(isset($this->sessions[$player])){
            $session = $this->sessions[$player];

            MultiVersion::getInstance()->getLogger()->debug(
                "Player {$playerName} quit (protocol: {$session->getProtocol()})"
            );

            $ev = new PlayerProtocolQuitEvent($player, $session->getProtocol());
            $ev->call();

            unset($this->sessions[$player]);
        }

        unset($this->errorCounts[$playerName]);

        MultiVersionAPI::unregisterPlayer($player);
    }
}
