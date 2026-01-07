<?php
declare(strict_types=1);
namespace MultiVersion\Translator;
final class ItemPalette{
    private int $protocol;
    private array $runtimeToServer = [];
    private array $serverToRuntime = [];
    private array $itemMap = [];
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

        foreach($paletteData as $runtimeId => $itemData){
            $this->itemMap[$runtimeId] = [
                'name' => $itemData['name'],
                'legacy_id' => $itemData['legacy_id'] ?? 0,
                'component_based' => $itemData['component_based'] ?? false
            ];
        }
    }
    private function buildMappings(): void{
        $serverPalette = $this->getServerPalette();

        foreach($this->itemMap as $clientRuntime => $clientItem){
            $serverRuntime = $this->findServerRuntime($clientItem, $serverPalette);
            if($serverRuntime !== null){
                $this->runtimeToServer[$clientRuntime] = $serverRuntime;
                $this->serverToRuntime[$serverRuntime] = $clientRuntime;
            }
        }
    }
    private function findServerRuntime(array $clientItem, array $serverPalette): ?int{
        foreach($serverPalette as $serverRuntime => $serverItem){
            if($serverItem['name'] === $clientItem['name']){
                return $serverRuntime;
            }
        }

        return 0;
    }
    public function translateToServer(int $clientRuntime): int{
        return $this->runtimeToServer[$clientRuntime] ?? 0;
    }
    public function translateToClient(int $serverRuntime): int{
        return $this->serverToRuntime[$serverRuntime] ?? 0;
    }
    public function getItemName(int $runtimeId): string{
        return $this->itemMap[$runtimeId]['name'] ?? 'minecraft:air';
    }
    public function isComponentBased(int $runtimeId): bool{
        return $this->itemMap[$runtimeId]['component_based'] ?? false;
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
            0 => ['name' => 'minecraft:air', 'legacy_id' => 0],
            1 => ['name' => 'minecraft:stone', 'legacy_id' => 1],
            2 => ['name' => 'minecraft:grass', 'legacy_id' => 2],
            3 => ['name' => 'minecraft:dirt', 'legacy_id' => 3],
            4 => ['name' => 'minecraft:cobblestone', 'legacy_id' => 4],
            5 => ['name' => 'minecraft:planks', 'legacy_id' => 5],
            6 => ['name' => 'minecraft:bedrock', 'legacy_id' => 7],
            7 => ['name' => 'minecraft:diamond', 'legacy_id' => 264],
            8 => ['name' => 'minecraft:iron_ingot', 'legacy_id' => 265],
            9 => ['name' => 'minecraft:gold_ingot', 'legacy_id' => 266],
            10 => ['name' => 'minecraft:stick', 'legacy_id' => 280]
        ];
    }
    private function getPalette594(): array{
        return [
            0 => ['name' => 'minecraft:air', 'legacy_id' => 0],
            1 => ['name' => 'minecraft:stone', 'legacy_id' => 1],
            2 => ['name' => 'minecraft:grass_block', 'legacy_id' => 2],
            3 => ['name' => 'minecraft:dirt', 'legacy_id' => 3],
            4 => ['name' => 'minecraft:cobblestone', 'legacy_id' => 4],
            5 => ['name' => 'minecraft:planks', 'legacy_id' => 5],
            6 => ['name' => 'minecraft:bedrock', 'legacy_id' => 7],
            7 => ['name' => 'minecraft:diamond', 'legacy_id' => 264, 'component_based' => true],
            8 => ['name' => 'minecraft:iron_ingot', 'legacy_id' => 265],
            9 => ['name' => 'minecraft:gold_ingot', 'legacy_id' => 266],
            10 => ['name' => 'minecraft:stick', 'legacy_id' => 280]
        ];
    }
    private function getPalette621(): array{
        return [
            0 => ['name' => 'minecraft:air', 'legacy_id' => 0],
            1 => ['name' => 'minecraft:stone', 'legacy_id' => 1],
            2 => ['name' => 'minecraft:grass_block', 'legacy_id' => 2],
            3 => ['name' => 'minecraft:dirt', 'legacy_id' => 3],
            4 => ['name' => 'minecraft:cobblestone', 'legacy_id' => 4],
            5 => ['name' => 'minecraft:planks', 'legacy_id' => 5],
            6 => ['name' => 'minecraft:bedrock', 'legacy_id' => 7],
            7 => ['name' => 'minecraft:diamond', 'legacy_id' => 264, 'component_based' => true],
            8 => ['name' => 'minecraft:iron_ingot', 'legacy_id' => 265],
            9 => ['name' => 'minecraft:gold_ingot', 'legacy_id' => 266],
            10 => ['name' => 'minecraft:stick', 'legacy_id' => 280]
        ];
    }
}
