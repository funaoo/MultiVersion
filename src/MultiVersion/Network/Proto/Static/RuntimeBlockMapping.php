<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Static;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\nbt\tag\CompoundTag;
class RuntimeBlockMapping{
    private static array $serverToClient = [];

    private static array $clientToServer = [];

    private static array $blockNameToClient = [];

    private static array $airIds = [];

    private static array $stoneFallback = [];

    private static array $loaded = [];
    public static function register(int $protocol, array $clientPalette): void{
        if(isset(self::$loaded[$protocol])){
            return;
        }

        $deserializer = GlobalBlockStateHandlers::getDeserializer();

        self::$serverToClient[$protocol] = [];
        self::$clientToServer[$protocol] = [];
        self::$blockNameToClient[$protocol] = [];

        $airFound = false;
        $stoneFound = false;

        foreach($clientPalette as $clientRuntimeId => $blockData){
            $name = $blockData['name'];
            $states = $blockData['states'] ?? [];

            if($name === 'minecraft:air'){
                self::$airIds[$protocol] = $clientRuntimeId;
                $airFound = true;
            }

            if($name === 'minecraft:stone' && empty($states)){
                self::$stoneFallback[$protocol] = $clientRuntimeId;
                $stoneFound = true;
            }

            self::$blockNameToClient[$protocol][$name] = $clientRuntimeId;

            try{
                $blockState = BlockStateData::current($name, $states);
                $serverStateId = $deserializer->deserialize($blockState);

                self::$serverToClient[$protocol][$serverStateId] = $clientRuntimeId;
                self::$clientToServer[$protocol][$clientRuntimeId] = $serverStateId;
            }catch(\Exception $e){
                self::$clientToServer[$protocol][$clientRuntimeId] = self::$airIds[$protocol] ?? 0;
            }
        }

        if(!$airFound){
            self::$airIds[$protocol] = 0;
        }
        if(!$stoneFound){
            self::$stoneFallback[$protocol] = 1;
        }

        self::$loaded[$protocol] = true;
    }
    private static function findFallbackBlock(int $protocol, string $serverBlockName): int{
        $fallbackName = BlockFallbackRegistry::findFallback($serverBlockName);

        if(isset(self::$blockNameToClient[$protocol][$fallbackName])){
            return self::$blockNameToClient[$protocol][$fallbackName];
        }

        $baseName = str_replace('minecraft:', '', $fallbackName);
        foreach(self::$blockNameToClient[$protocol] as $clientName => $clientId){
            if(str_contains($clientName, $baseName)){
                return $clientId;
            }
        }

        return self::$stoneFallback[$protocol] ?? 1;
    }
    private static function getBaseBlockName(string $blockName): string{
        return BlockFallbackRegistry::findFallback($blockName);
    }
    private static function getBlockVariants(string $blockName): array{
        return [BlockFallbackRegistry::findFallback($blockName)];
    }
    public static function serverToClient(int $protocol, int $serverRuntimeId): int{
        if(!isset(self::$loaded[$protocol])){
            throw new \RuntimeException("Protocol $protocol not registered");
        }

        return self::$serverToClient[$protocol][$serverRuntimeId]
            ?? self::$stoneFallback[$protocol]
            ?? 1;
    }
    public static function clientToServer(int $protocol, int $clientRuntimeId): int{
        if(!isset(self::$loaded[$protocol])){
            throw new \RuntimeException("Protocol $protocol not registered");
        }

        return self::$clientToServer[$protocol][$clientRuntimeId]
            ?? (self::$airIds[$protocol] ?? 0);
    }
    public static function hasBlock(int $protocol, string $blockName): bool{
        return isset(self::$blockNameToClient[$protocol][$blockName]);
    }
    public static function getStats(int $protocol): array{
        if(!isset(self::$loaded[$protocol])){
            return ['loaded' => false];
        }

        return [
            'loaded' => true,
            'server_to_client' => count(self::$serverToClient[$protocol] ?? []),
            'client_to_server' => count(self::$clientToServer[$protocol] ?? []),
            'known_blocks' => count(self::$blockNameToClient[$protocol] ?? []),
            'air_id' => self::$airIds[$protocol] ?? 0,
            'stone_fallback' => self::$stoneFallback[$protocol] ?? 1
        ];
    }
    public static function validate(int $protocol): bool{
        if(!isset(self::$loaded[$protocol])){
            return false;
        }

        foreach(self::$serverToClient[$protocol] as $serverId => $clientId){
            if(!isset(self::$clientToServer[$protocol][$clientId])){
                return false;
            }
        }

        return true;
    }
}
