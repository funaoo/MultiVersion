<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Adapter;
use MultiVersion\Network\Session\ProtocolSession;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\LevelChunkPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
interface ProtocolAdapter{
    public function getProtocolVersion(): int;
    public function getVersionString(): string;
    public function translateStartGame(StartGamePacket $packet, ProtocolSession $session): StartGamePacket;
    public function translateLevelChunk(LevelChunkPacket $packet, ProtocolSession $session): LevelChunkPacket;
    public function translateUpdateBlock(UpdateBlockPacket $packet, ProtocolSession $session): UpdateBlockPacket;
    public function translateInventoryContent(InventoryContentPacket $packet, ProtocolSession $session): InventoryContentPacket;
    public function translateInventorySlot(InventorySlotPacket $packet, ProtocolSession $session): InventorySlotPacket;
    public function translateAddActor(AddActorPacket $packet, ProtocolSession $session): AddActorPacket;
    public function translateSetActorData(SetActorDataPacket $packet, ProtocolSession $session): SetActorDataPacket;
}
