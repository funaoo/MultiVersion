<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\NetworkSession;
abstract class PacketTranslator{
    public const PROTOCOL_VERSION = null;

    public const RAKNET_VERSION = null;

    public const ENCRYPTION_CONTEXT = true;

    public const MINECRAFT_VERSION = "Unknown";
    abstract public function handleOutgoing(ClientboundPacket $packet): ?ClientboundPacket;
    abstract public function handleIncoming(ServerboundPacket $packet): ?ServerboundPacket;
    abstract public function createInGameHandler(NetworkSession $session): ?PacketHandler;
    public function injectClientData(array &$clientData): void{
    }
    abstract public function getTypeConverter(): TypeConverter;
    public function getProtocolName(): string{
        return "Protocol " . static::PROTOCOL_VERSION . " (" . static::MINECRAFT_VERSION . ")";
    }
}
