<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\v527;
use MultiVersion\Network\Proto\PacketTranslator;
use MultiVersion\Network\Proto\TypeConverter;
use MultiVersion\Network\Proto\Static\RuntimeBlockMapping;
use MultiVersion\Network\Proto\Static\RuntimeItemMapping;
use MultiVersion\Network\Proto\Packets\CustomStartGamePacket;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\LevelChunkPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\NetworkSession;
class v527PacketTranslator extends PacketTranslator{
    public const PROTOCOL_VERSION = 527;
    public const RAKNET_VERSION = 10;
    public const MINECRAFT_VERSION = "1.19.2";
    public const ENCRYPTION_CONTEXT = false;
    private v527TypeConverter $typeConverter;
    private bool $palettesLoaded = false;
    public function __construct(){
        $this->typeConverter = new v527TypeConverter();
        $this->loadPalettes();
    }
    private function loadPalettes(): void{
        $resourceDir = dirname(__DIR__, 5) . '/resources/v527/';
        $blockPaletteFile = $resourceDir . 'canonical_block_states.nbt';
        $itemTableFile = $resourceDir . 'required_item_list.json';

        $blockPalette = null;
        $itemTable = null;

        if(file_exists($blockPaletteFile)){
            try{
                $blockPalette = \MultiVersion\Network\Proto\Static\PaletteLoader::loadBlockPalette($blockPaletteFile);
            }catch(\Exception $e){
            }
        }

        if(file_exists($itemTableFile)){
            try{
                $itemTable = \MultiVersion\Network\Proto\Static\PaletteLoader::loadItemTable($itemTableFile);
            }catch(\Exception $e){
            }
        }

        if($blockPalette === null){
            $blockPalette = \MultiVersion\Network\Proto\Static\PaletteLoader::createMinimalBlockPalette();
        }

        if($itemTable === null){
            $itemTable = \MultiVersion\Network\Proto\Static\PaletteLoader::createMinimalItemTable();
        }

        RuntimeBlockMapping::register(self::PROTOCOL_VERSION, $blockPalette);
        RuntimeItemMapping::register(self::PROTOCOL_VERSION, $itemTable);
        \MultiVersion\Network\Proto\Static\ItemTranslator::register(self::PROTOCOL_VERSION, $itemTable);

        $this->palettesLoaded = true;
    }
    private function getDefaultBlockPalette(): array{
        return \MultiVersion\Network\Proto\Static\PaletteLoader::createMinimalBlockPalette();
    }
    private function getDefaultItemPalette(): array{
        return \MultiVersion\Network\Proto\Static\PaletteLoader::createMinimalItemTable();
    }
    public function handleOutgoing(ClientboundPacket $packet): ?ClientboundPacket{
        if($packet instanceof StartGamePacket){
            return $this->translateStartGame($packet);
        }

        if($packet instanceof UpdateBlockPacket){
            $packet->blockRuntimeId = RuntimeBlockMapping::serverToClient(
                self::PROTOCOL_VERSION,
                $packet->blockRuntimeId
            );
            return $packet;
        }

        if($packet instanceof LevelEventPacket){
            if($packet->eventId === LevelEvent::PARTICLE_DESTROY){
                $packet->eventData = RuntimeBlockMapping::serverToClient(
                    self::PROTOCOL_VERSION,
                    $packet->eventData
                );
            }elseif($packet->eventId === LevelEvent::PARTICLE_PUNCH_BLOCK){
                $blockId = $packet->eventData & 0xFFFFFF;
                $translated = RuntimeBlockMapping::serverToClient(self::PROTOCOL_VERSION, $blockId);
                $packet->eventData = ($packet->eventData & 0xFF000000) | $translated;
            }
            return $packet;
        }

        if($packet instanceof LevelSoundEventPacket){
            if(in_array($packet->sound, [
                LevelSoundEvent::BREAK,
                LevelSoundEvent::PLACE,
                LevelSoundEvent::HIT,
                LevelSoundEvent::LAND,
                LevelSoundEvent::ITEM_USE_ON
            ], true) && $packet->extraData !== -1){
                $packet->extraData = RuntimeBlockMapping::serverToClient(
                    self::PROTOCOL_VERSION,
                    $packet->extraData
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
    private function translateStartGame(StartGamePacket $packet): StartGamePacket{
        $custom = CustomStartGamePacket::fromOriginal($packet);
        $custom->setCustomBlockPalette($this->getDefaultBlockPalette());
        $custom->setCustomItemTable($this->getDefaultItemPalette());
        return $custom;
    }
    public function handleIncoming(ServerboundPacket $packet): ?ServerboundPacket{
        if($packet instanceof InventoryTransactionPacket){
            return $packet;
        }

        return $packet;
    }
    public function createInGameHandler(NetworkSession $session): ?PacketHandler{
        return null;
    }
    public function injectClientData(array &$clientData): void{
        $clientData["IsEditorMode"] = false;
        $clientData["TrustedSkin"] = true;
        $clientData["CompatibleWithClientSideChunkGen"] = false;
    }
    public function getTypeConverter(): TypeConverter{
        return $this->typeConverter;
    }
}
