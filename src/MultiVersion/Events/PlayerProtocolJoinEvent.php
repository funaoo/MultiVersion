<?php
declare(strict_types=1);
namespace MultiVersion\Events;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;
final class PlayerProtocolJoinEvent extends PlayerEvent{
    private int $protocol;
    private string $clientVersion;
    public function __construct(Player $player, int $protocol, string $clientVersion){
        $this->player = $player;
        $this->protocol = $protocol;
        $this->clientVersion = $clientVersion;
    }
    public function getProtocol(): int{
        return $this->protocol;
    }
    public function getClientVersion(): string{
        return $this->clientVersion;
    }
    public function isLegacy(): bool{
        return $this->protocol < \pocketmine\network\mcpe\protocol\ProtocolInfo::CURRENT_PROTOCOL;
    }
}
