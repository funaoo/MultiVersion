<?php
declare(strict_types=1);
namespace MultiVersion\Events;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;
final class PlayerProtocolQuitEvent extends PlayerEvent{
    private int $protocol;
    public function __construct(Player $player, int $protocol){
        $this->player = $player;
        $this->protocol = $protocol;
    }
    public function getProtocol(): int{
        return $this->protocol;
    }
    public function wasLegacy(): bool{
        return $this->protocol < \pocketmine\network\mcpe\protocol\ProtocolInfo::CURRENT_PROTOCOL;
    }
}
