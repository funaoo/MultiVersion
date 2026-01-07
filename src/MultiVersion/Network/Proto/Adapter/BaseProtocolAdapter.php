<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Adapter;
use MultiVersion\Network\Proto\Palette\BlockPaletteManager;
use MultiVersion\Network\Proto\Palette\ItemPaletteManager;
use MultiVersion\Network\Proto\Metadata\EntityMetadataMapper;
use MultiVersion\Network\Proto\Translation\ChunkTranslator;
use MultiVersion\Network\Proto\Translation\ItemTranslator;
use MultiVersion\Network\Session\ProtocolSession;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\LevelChunkPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
abstract class BaseProtocolAdapter implements ProtocolAdapter{
    abstract public function getProtocolVersion(): int;
    abstract public function getVersionString(): string;
    public function translateStartGame(StartGamePacket $packet, ProtocolSession $session): StartGamePacket{
        return $packet;
    }
    public function translateLevelChunk(LevelChunkPacket $packet, ProtocolSession $session): LevelChunkPacket{
        $blockPalette = BlockPaletteManager::getInstance()->getPalette($this->getProtocolVersion());

        $data = $packet->getData();
        $newData = ChunkTranslator::translateChunkData($data, $blockPalette);

        $translated = clone $packet;
        $translated->data = $newData;

        return $translated;
    }
    public function translateUpdateBlock(UpdateBlockPacket $packet, ProtocolSession $session): UpdateBlockPacket{
        $blockPalette = BlockPaletteManager::getInstance()->getPalette($this->getProtocolVersion());

        $translated = clone $packet;
        $translated->blockRuntimeId = $blockPalette->toRuntimeId($packet->blockRuntimeId);

        return $translated;
    }
    public function translateInventoryContent(InventoryContentPacket $packet, ProtocolSession $session): InventoryContentPacket{
        $itemPalette = ItemPaletteManager::getInstance()->getPalette($this->getProtocolVersion());

        $translated = clone $packet;
        $translated->items = ItemTranslator::translateItemArray($packet->items, $itemPalette);

        return $translated;
    }
    public function translateInventorySlot(InventorySlotPacket $packet, ProtocolSession $session): InventorySlotPacket{
        $itemPalette = ItemPaletteManager::getInstance()->getPalette($this->getProtocolVersion());

        $translated = clone $packet;
        $translated->item = ItemTranslator::translateItemStackWrapper($packet->item, $itemPalette);

        return $translated;
    }
    public function translateAddActor(AddActorPacket $packet, ProtocolSession $session): AddActorPacket{
        $metadataMapper = EntityMetadataMapper::getInstance()->getMapping($this->getProtocolVersion());

        $translated = clone $packet;
        $translated->metadata = $metadataMapper->translate($packet->metadata);

        return $translated;
    }
    public function translateSetActorData(SetActorDataPacket $packet, ProtocolSession $session): SetActorDataPacket{
        $metadataMapper = EntityMetadataMapper::getInstance()->getMapping($this->getProtocolVersion());

        $translated = clone $packet;
        $translated->metadata = $metadataMapper->translate($packet->metadata);

        return $translated;
    }
}
