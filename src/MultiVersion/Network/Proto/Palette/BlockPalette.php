<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Palette;
final class BlockPalette{
    private int $protocol;
    private array $mapping;
    private array $reverseMapping;
    public function __construct(int $protocol, array $mapping, array $reverseMapping){
        $this->protocol = $protocol;
        $this->mapping = $mapping;
        $this->reverseMapping = $reverseMapping;
    }
    public function getProtocol(): int{
        return $this->protocol;
    }
    public function toRuntimeId(int $typeId): int{
        return $this->mapping[$typeId] ?? $typeId;
    }
    public function fromRuntimeId(int $runtimeId): int{
        return $this->reverseMapping[$runtimeId] ?? $runtimeId;
    }
    public function hasMapping(int $typeId): bool{
        return isset($this->mapping[$typeId]);
    }
}
