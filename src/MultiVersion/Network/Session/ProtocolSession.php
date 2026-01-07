<?php
declare(strict_types=1);
namespace MultiVersion\Network\Session;
use MultiVersion\Network\Proto\Adapter\ProtocolAdapter;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
final class ProtocolSession{
    private Player $player;
    private int $protocol;
    private ProtocolAdapter $adapter;
    public function __construct(Player $player, int $protocol, ProtocolAdapter $adapter){
        $this->player = $player;
        $this->protocol = $protocol;
        $this->adapter = $adapter;
    }
    public function getPlayer(): Player{
        return $this->player;
    }
    public function getProtocol(): int{
        return $this->protocol;
    }
    public function getAdapter(): ProtocolAdapter{
        return $this->adapter;
    }
    public function isNativeProtocol(): bool{
        return $this->protocol === ProtocolInfo::CURRENT_PROTOCOL;
    }
}
