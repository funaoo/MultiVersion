<?php
declare(strict_types=1);
namespace MultiVersion\Core;
use MultiVersion\MultiVersion;
use MultiVersion\Protocol\ProtocolInterface;
use MultiVersion\Protocol\Versions\Protocol527;
use MultiVersion\Protocol\Versions\Protocol594;
use MultiVersion\Protocol\Versions\Protocol621;
use MultiVersion\Translator\BlockPalette;
use MultiVersion\Translator\ItemPalette;
use MultiVersion\Translator\EntityPalette;
final class VersionRegistry{
    private MultiVersion $plugin;
    private array $protocols = [];
    private array $sessions = [];
    private array $blockPalettes = [];
    private array $itemPalettes = [];
    private array $entityPalettes = [];
    private array $protocolVersionMap = [
        527 => "1.19.x",
        594 => "1.20.0-1.20.30",
        621 => "1.20.40+"
    ];
    public function __construct(MultiVersion $plugin){
        $this->plugin = $plugin;
        $this->registerProtocols();
        $this->initializePalettes();
    }
    private function registerProtocols(): void{
        $this->protocols = [
            621 => new Protocol621(),
            594 => new Protocol594(),
            527 => new Protocol527()
        ];
    }
    private function initializePalettes(): void{
        foreach(array_keys($this->protocols) as $protocol){
            $this->blockPalettes[$protocol] = new BlockPalette($protocol);
            $this->itemPalettes[$protocol] = new ItemPalette($protocol);
            $this->entityPalettes[$protocol] = new EntityPalette($protocol);
        }
    }
    public function register(string $playerName, int $protocol): void{
        if(!$this->isProtocolSupported($protocol)){
            return;
        }
        $this->sessions[$playerName] = [
            "protocol" => $protocol,
            "time" => microtime(true),
            "version" => $this->protocolVersionMap[$protocol] ?? "Unknown"
        ];
    }
    public function unregister(string $playerName): void{
        unset($this->sessions[$playerName]);
    }
    public function getProtocol(string $playerName): ?int{
        return $this->sessions[$playerName]["protocol"] ?? null;
    }
    public function getProtocolInterface(int $protocol): ?ProtocolInterface{
        return $this->protocols[$protocol] ?? null;
    }
    public function isProtocolSupported(int $protocol): bool{
        return isset($this->protocols[$protocol]);
    }
    public function isProtocolActive(int $protocol): bool{
        foreach($this->sessions as $session){
            if($session['protocol'] === $protocol){
                return true;
            }
        }
        return false;
    }
    public function getSupportedProtocols(): array{
        $result = [];
        foreach($this->protocols as $protocol => $interface){
            $result[$protocol] = $this->protocolVersionMap[$protocol] ?? "Unknown";
        }
        return $result;
    }
    public function getActiveSessions(): array{
        return $this->sessions;
    }
    public function getActiveSessionCount(): int{
        return count($this->sessions);
    }
    public function getBlockPalette(int $protocol): ?BlockPalette{
        return $this->blockPalettes[$protocol] ?? null;
    }
    public function getItemPalette(int $protocol): ?ItemPalette{
        return $this->itemPalettes[$protocol] ?? null;
    }
    public function getEntityPalette(int $protocol): ?EntityPalette{
        return $this->entityPalettes[$protocol] ?? null;
    }
    public function reload(): void{
        $this->blockPalettes = [];
        $this->itemPalettes = [];
        $this->entityPalettes = [];
        $this->initializePalettes();
    }
}
