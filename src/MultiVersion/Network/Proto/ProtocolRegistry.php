<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto;
use MultiVersion\Network\Proto\Adapter\ProtocolAdapter;
use MultiVersion\Network\Proto\Versions\v594\Protocol594Adapter;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
final class ProtocolRegistry{
    private array $adapters = [];
    private array $supported = [];
    private int $nativeProtocol;
    public function __construct(){
        $this->nativeProtocol = ProtocolInfo::CURRENT_PROTOCOL;
        $this->registerDefaults();
    }
    private function registerDefaults(): void{
        $this->register(594, new \MultiVersion\Network\Proto\Versions\v594\Protocol594Adapter());
        $this->register(618, new \MultiVersion\Network\Proto\Versions\v618\Protocol618Adapter());
        $this->register(630, new \MultiVersion\Network\Proto\Versions\v630\Protocol630Adapter());
    }
    public function register(int $protocol, ProtocolAdapter $adapter): void{
        $this->adapters[$protocol] = $adapter;
        $this->supported[$protocol] = true;
    }
    public function isSupported(int $protocol): bool{
        return isset($this->supported[$protocol]) || $protocol === $this->nativeProtocol;
    }
    public function getAdapter(int $protocol): ProtocolAdapter{
        if(!isset($this->adapters[$protocol])){
            throw new \RuntimeException("Protocol {$protocol} not registered");
        }
        return $this->adapters[$protocol];
    }
    public function getNativeProtocol(): int{
        return $this->nativeProtocol;
    }
    public function getSupportedProtocols(): array{
        return array_keys($this->supported);
    }
}
