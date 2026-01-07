<?php
declare(strict_types=1);
namespace MultiVersion\Translator;
final class BlockPalette{
    private int $protocol;
    private array $runtimeToServer = [];
    private array $serverToRuntime = [];
    private array $blockStateMap = [];
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

        foreach($paletteData as $runtimeId => $blockData){
            $this->blockStateMap[$runtimeId] = [
                'name' => $blockData['name'],
                'states' => $blockData['states'] ?? [],
                'legacy_id' => $blockData['legacy_id'] ?? 0
            ];
        }
    }
    private function buildMappings(): void{
        $serverPalette = $this->getServerPalette();

        foreach($this->blockStateMap as $clientRuntime => $clientBlock){
            $serverRuntime = $this->findServerRuntime($clientBlock, $serverPalette);
            if($serverRuntime !== null){
                $this->runtimeToServer[$clientRuntime] = $serverRuntime;
                $this->serverToRuntime[$serverRuntime] = $clientRuntime;
            }
        }
    }
    private function findServerRuntime(array $clientBlock, array $serverPalette): ?int{
        foreach($serverPalette as $serverRuntime => $serverBlock){
            if($serverBlock['name'] === $clientBlock['name']){
                if($this->statesMatch($clientBlock['states'], $serverBlock['states'])){
                    return $serverRuntime;
                }
            }
        }

        foreach($serverPalette as $serverRuntime => $serverBlock){
            if($serverBlock['name'] === $clientBlock['name']){
                return $serverRuntime;
            }
        }

        return 0;
    }
    private function statesMatch(array $states1, array $states2): bool{
        if(empty($states1) && empty($states2)){
            return true;
        }

        foreach($states1 as $key => $value){
            if(!isset($states2[$key]) || $states2[$key] !== $value){
                return false;
            }
        }

        return true;
    }
    public function translateToServer(int $clientRuntime): int{
        return $this->runtimeToServer[$clientRuntime] ?? 0;
    }
    public function translateToClient(int $serverRuntime): int{
        return $this->serverToRuntime[$serverRuntime] ?? 0;
    }
    public function getBlockName(int $runtimeId): string{
        return $this->blockStateMap[$runtimeId]['name'] ?? 'minecraft:air';
    }
    public function getBlockStates(int $runtimeId): array{
        return $this->blockStateMap[$runtimeId]['states'] ?? [];
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
            0 => ['name' => 'minecraft:air', 'states' => [], 'legacy_id' => 0],
            1 => ['name' => 'minecraft:stone', 'states' => [], 'legacy_id' => 1],
            2 => ['name' => 'minecraft:grass', 'states' => [], 'legacy_id' => 2],
            3 => ['name' => 'minecraft:dirt', 'states' => [], 'legacy_id' => 3],
            4 => ['name' => 'minecraft:cobblestone', 'states' => [], 'legacy_id' => 4],
            5 => ['name' => 'minecraft:planks', 'states' => [], 'legacy_id' => 5],
            6 => ['name' => 'minecraft:bedrock', 'states' => [], 'legacy_id' => 7],
            7 => ['name' => 'minecraft:sand', 'states' => [], 'legacy_id' => 12],
            8 => ['name' => 'minecraft:gravel', 'states' => [], 'legacy_id' => 13],
            9 => ['name' => 'minecraft:log', 'states' => [], 'legacy_id' => 17],
            10 => ['name' => 'minecraft:leaves', 'states' => [], 'legacy_id' => 18]
        ];
    }
    private function getPalette594(): array{
        return [
            0 => ['name' => 'minecraft:air', 'states' => [], 'legacy_id' => 0],
            1 => ['name' => 'minecraft:stone', 'states' => [], 'legacy_id' => 1],
            2 => ['name' => 'minecraft:grass_block', 'states' => [], 'legacy_id' => 2],
            3 => ['name' => 'minecraft:dirt', 'states' => [], 'legacy_id' => 3],
            4 => ['name' => 'minecraft:cobblestone', 'states' => [], 'legacy_id' => 4],
            5 => ['name' => 'minecraft:planks', 'states' => [], 'legacy_id' => 5],
            6 => ['name' => 'minecraft:bedrock', 'states' => [], 'legacy_id' => 7],
            7 => ['name' => 'minecraft:sand', 'states' => [], 'legacy_id' => 12],
            8 => ['name' => 'minecraft:gravel', 'states' => [], 'legacy_id' => 13],
            9 => ['name' => 'minecraft:oak_log', 'states' => [], 'legacy_id' => 17],
            10 => ['name' => 'minecraft:oak_leaves', 'states' => [], 'legacy_id' => 18]
        ];
    }
    private function getPalette621(): array{
        return [
            0 => ['name' => 'minecraft:air', 'states' => [], 'legacy_id' => 0],
            1 => ['name' => 'minecraft:stone', 'states' => [], 'legacy_id' => 1],
            2 => ['name' => 'minecraft:grass_block', 'states' => [], 'legacy_id' => 2],
            3 => ['name' => 'minecraft:dirt', 'states' => [], 'legacy_id' => 3],
            4 => ['name' => 'minecraft:cobblestone', 'states' => [], 'legacy_id' => 4],
            5 => ['name' => 'minecraft:planks', 'states' => [], 'legacy_id' => 5],
            6 => ['name' => 'minecraft:bedrock', 'states' => [], 'legacy_id' => 7],
            7 => ['name' => 'minecraft:sand', 'states' => [], 'legacy_id' => 12],
            8 => ['name' => 'minecraft:gravel', 'states' => [], 'legacy_id' => 13],
            9 => ['name' => 'minecraft:oak_log', 'states' => [], 'legacy_id' => 17],
            10 => ['name' => 'minecraft:oak_leaves', 'states' => [], 'legacy_id' => 18]
        ];
    }
}
