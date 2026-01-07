<?php
declare(strict_types=1);
namespace MultiVersion\Core;
use MultiVersion\MultiVersion;
use pocketmine\network\mcpe\protocol\DataPacket;
final class PacketRouter{
    private MultiVersion $plugin;
    private int $packetsRouted = 0;
    private bool $operational = true;
    public function __construct(MultiVersion $plugin){
        $this->plugin = $plugin;
    }
    public function route(DataPacket $packet, string $playerName): bool{
        $this->packetsRouted++;
        $this->plugin->getServerManager()->incrementPacketsTranslated();
        return true;
    }
    public function getPacketsRouted(): int{
        return $this->packetsRouted;
    }
    public function isOperational(): bool{
        return $this->operational;
    }
}
