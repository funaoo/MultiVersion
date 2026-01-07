<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\v594;
use MultiVersion\Network\Proto\PacketTranslator;
use MultiVersion\Network\Proto\TypeConverter;
use MultiVersion\Network\Proto\Static\RuntimeBlockMapping;
use MultiVersion\Network\Proto\Static\RuntimeItemMapping;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\NetworkSession;
class v594PacketTranslator extends PacketTranslator{
    public const PROTOCOL_VERSION = 594;
    public const RAKNET_VERSION = 11;
    public const MINECRAFT_VERSION = "1.20.0-1.20.30";
    private v594TypeConverter $typeConverter;
    public function __construct(){
        $this->typeConverter = new v594TypeConverter();
        $this->loadPalettes();
    }
    private function loadPalettes(): void{
        $blockPalette = [
            ['name' => 'minecraft:air', 'states' => []],
            ['name' => 'minecraft:stone', 'states' => []],
            ['name' => 'minecraft:grass_block', 'states' => []],
            ['name' => 'minecraft:dirt', 'states' => []],
            ['name' => 'minecraft:cobblestone', 'states' => []],
        ];

        $itemPalette = [
            0 => ['name' => 'minecraft:air', 'server_id' => 0],
            1 => ['name' => 'minecraft:stone', 'server_id' => 1],
        ];

        RuntimeBlockMapping::register(self::PROTOCOL_VERSION, $blockPalette);
        RuntimeItemMapping::register(self::PROTOCOL_VERSION, $itemPalette);
    }
    public function handleOutgoing(ClientboundPacket $packet): ?ClientboundPacket{
        if($packet instanceof UpdateBlockPacket){
            $translated = RuntimeBlockMapping::serverToClient(
                self::PROTOCOL_VERSION,
                $packet->blockRuntimeId
            );
            if($translated !== $packet->blockRuntimeId){
                $packet->blockRuntimeId = $translated;
            }
            return $packet;
        }

        if($packet instanceof LevelEventPacket){
            if($packet->eventId === LevelEvent::PARTICLE_DESTROY){
                $packet->eventData = RuntimeBlockMapping::serverToClient(
                    self::PROTOCOL_VERSION,
                    $packet->eventData
                );
            }
            return $packet;
        }

        if($packet instanceof InventoryContentPacket){
            foreach($packet->items as $key => $item){
                $packet->items[$key] = $this->typeConverter->stripUnsupportedItemTags($item);
            }
            return $packet;
        }

        if($packet instanceof InventorySlotPacket){
            $packet->item = $this->typeConverter->stripUnsupportedItemTags($packet->item);
            return $packet;
        }

        return $packet;
    }
    public function handleIncoming(ServerboundPacket $packet): ?ServerboundPacket{
        return $packet;
    }
    public function createInGameHandler(NetworkSession $session): ?PacketHandler{
        return null;
    }
    public function injectClientData(array &$clientData): void{
    }
    public function getTypeConverter(): TypeConverter{
        return $this->typeConverter;
    }
}
