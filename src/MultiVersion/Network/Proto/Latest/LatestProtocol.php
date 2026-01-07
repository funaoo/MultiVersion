<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Latest;
use MultiVersion\Network\Proto\PacketTranslator;
use MultiVersion\Network\Proto\TypeConverter;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\NetworkSession;
class LatestProtocol extends PacketTranslator{
    public const PROTOCOL_VERSION = ProtocolInfo::CURRENT_PROTOCOL;
    public const RAKNET_VERSION = 11;
    public const MINECRAFT_VERSION = ProtocolInfo::MINECRAFT_VERSION_NETWORK;
    private LatestTypeConverter $typeConverter;
    public function __construct(){
        $this->typeConverter = new LatestTypeConverter();
    }
    public function handleOutgoing(ClientboundPacket $packet): ?ClientboundPacket{
        return $packet;
    }
    public function handleIncoming(ServerboundPacket $packet): ?ServerboundPacket{
        return $packet;
    }
    public function createInGameHandler(NetworkSession $session): ?PacketHandler{
        return null;
    }
    public function getTypeConverter(): TypeConverter{
        return $this->typeConverter;
    }
}
