<?php
declare(strict_types=1);
namespace MultiVersion\Translator;
final class EntityPalette{
    private int $protocol;
    private array $runtimeToServer = [];
    private array $serverToRuntime = [];
    private array $entityMap = [];
    private bool $loaded = false;
    public function __construct(int $protocol){
        $this->protocol = $protocol;
        $this->initialize();
    }
    private function initialize(): void{
        $this->loadPalette();
        $this->buildMappings();
        $this->loaded = true;
    }
    private function loadPalette(): void{
        $paletteData = $this->getProtocolPalette($this->protocol);

        foreach($paletteData as $runtimeId => $entityData){
            $this->entityMap[$runtimeId] = [
                'identifier' => $entityData['identifier'],
                'legacy_id' => $entityData['legacy_id'] ?? 0,
                'summonable' => $entityData['summonable'] ?? true
            ];
        }
    }
    private function buildMappings(): void{
        $serverPalette = $this->getServerPalette();

        foreach($this->entityMap as $clientRuntime => $clientEntity){
            $serverRuntime = $this->findServerRuntime($clientEntity, $serverPalette);
            if($serverRuntime !== null){
                $this->runtimeToServer[$clientRuntime] = $serverRuntime;
                $this->serverToRuntime[$serverRuntime] = $clientRuntime;
            }
        }
    }
    private function findServerRuntime(array $clientEntity, array $serverPalette): ?int{
        foreach($serverPalette as $serverRuntime => $serverEntity){
            if($serverEntity['identifier'] === $clientEntity['identifier']){
                return $serverRuntime;
            }
        }

        return 1;
    }
    public function translateToServer(int $clientRuntime): int{
        return $this->runtimeToServer[$clientRuntime] ?? 1;
    }
    public function translateToClient(int $serverRuntime): int{
        return $this->serverToRuntime[$serverRuntime] ?? 1;
    }
    public function getEntityIdentifier(int $runtimeId): string{
        return $this->entityMap[$runtimeId]['identifier'] ?? 'minecraft:unknown';
    }
    public function isSummonable(int $runtimeId): bool{
        return $this->entityMap[$runtimeId]['summonable'] ?? true;
    }
    public function isLoaded(): bool{
        return $this->loaded;
    }
    private function getProtocolPalette(int $protocol): array{
        switch($protocol){
            case 527:
                return $this->getPalette527();
            case 594:
                return $this->getPalette594();
            case 621:
                return $this->getPalette621();
            default:
                return [];
        }
    }
    private function getServerPalette(): array{
        return $this->getPalette621();
    }
    private function getPalette527(): array{
        return [
            1 => ['identifier' => 'minecraft:player', 'legacy_id' => 63, 'summonable' => false],
            10 => ['identifier' => 'minecraft:chicken', 'legacy_id' => 10, 'summonable' => true],
            11 => ['identifier' => 'minecraft:cow', 'legacy_id' => 11, 'summonable' => true],
            12 => ['identifier' => 'minecraft:pig', 'legacy_id' => 12, 'summonable' => true],
            13 => ['identifier' => 'minecraft:sheep', 'legacy_id' => 13, 'summonable' => true],
            32 => ['identifier' => 'minecraft:zombie', 'legacy_id' => 32, 'summonable' => true],
            33 => ['identifier' => 'minecraft:creeper', 'legacy_id' => 33, 'summonable' => true],
            34 => ['identifier' => 'minecraft:skeleton', 'legacy_id' => 34, 'summonable' => true],
            35 => ['identifier' => 'minecraft:spider', 'legacy_id' => 35, 'summonable' => true],
            64 => ['identifier' => 'minecraft:item', 'legacy_id' => 64, 'summonable' => false]
        ];
    }
    private function getPalette594(): array{
        return [
            1 => ['identifier' => 'minecraft:player', 'legacy_id' => 63, 'summonable' => false],
            10 => ['identifier' => 'minecraft:chicken', 'legacy_id' => 10, 'summonable' => true],
            11 => ['identifier' => 'minecraft:cow', 'legacy_id' => 11, 'summonable' => true],
            12 => ['identifier' => 'minecraft:pig', 'legacy_id' => 12, 'summonable' => true],
            13 => ['identifier' => 'minecraft:sheep', 'legacy_id' => 13, 'summonable' => true],
            32 => ['identifier' => 'minecraft:zombie', 'legacy_id' => 32, 'summonable' => true],
            33 => ['identifier' => 'minecraft:creeper', 'legacy_id' => 33, 'summonable' => true],
            34 => ['identifier' => 'minecraft:skeleton', 'legacy_id' => 34, 'summonable' => true],
            35 => ['identifier' => 'minecraft:spider', 'legacy_id' => 35, 'summonable' => true],
            64 => ['identifier' => 'minecraft:item', 'legacy_id' => 64, 'summonable' => false],
            128 => ['identifier' => 'minecraft:allay', 'legacy_id' => 134, 'summonable' => true]
        ];
    }
    private function getPalette621(): array{
        return [
            1 => ['identifier' => 'minecraft:player', 'legacy_id' => 63, 'summonable' => false],
            10 => ['identifier' => 'minecraft:chicken', 'legacy_id' => 10, 'summonable' => true],
            11 => ['identifier' => 'minecraft:cow', 'legacy_id' => 11, 'summonable' => true],
            12 => ['identifier' => 'minecraft:pig', 'legacy_id' => 12, 'summonable' => true],
            13 => ['identifier' => 'minecraft:sheep', 'legacy_id' => 13, 'summonable' => true],
            32 => ['identifier' => 'minecraft:zombie', 'legacy_id' => 32, 'summonable' => true],
            33 => ['identifier' => 'minecraft:creeper', 'legacy_id' => 33, 'summonable' => true],
            34 => ['identifier' => 'minecraft:skeleton', 'legacy_id' => 34, 'summonable' => true],
            35 => ['identifier' => 'minecraft:spider', 'legacy_id' => 35, 'summonable' => true],
            64 => ['identifier' => 'minecraft:item', 'legacy_id' => 64, 'summonable' => false],
            128 => ['identifier' => 'minecraft:allay', 'legacy_id' => 134, 'summonable' => true]
        ];
    }
}
