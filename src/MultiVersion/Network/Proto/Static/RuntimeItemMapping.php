<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Static;
class RuntimeItemMapping{
    private static array $serverToClient = [];

    private static array $clientToServer = [];

    private static array $itemExistence = [];
    public static function register(int $protocol, array $clientItems): void{
        self::$serverToClient[$protocol] = [];
        self::$clientToServer[$protocol] = [];
        self::$itemExistence[$protocol] = [];

        foreach($clientItems as $clientItemId => $itemData){
            $name = $itemData['name'];
            $serverItemId = $itemData['server_id'] ?? $clientItemId;

            self::$itemExistence[$protocol][$name] = true;
            self::$serverToClient[$protocol][$serverItemId] = $clientItemId;
            self::$clientToServer[$protocol][$clientItemId] = $serverItemId;
        }
    }
    public static function serverToClient(int $protocol, int $serverItemId): int{
        return self::$serverToClient[$protocol][$serverItemId] ?? 0;
    }
    public static function clientToServer(int $protocol, int $clientItemId): int{
        return self::$clientToServer[$protocol][$clientItemId] ?? 0;
    }
    public static function hasItem(int $protocol, string $itemName): bool{
        return self::$itemExistence[$protocol][$itemName] ?? false;
    }
}
