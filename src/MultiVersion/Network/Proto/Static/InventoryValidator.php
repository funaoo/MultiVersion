<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Static;
use pocketmine\player\Player;
use pocketmine\item\Item;
final class InventoryValidator{
    private static array $playerInventoryState = [];
    private static array $pendingTransactions = [];
    public static function snapshotInventory(Player $player): void{
        $name = $player->getName();
        $inventory = $player->getInventory();

        $snapshot = [];
        foreach($inventory->getContents() as $slot => $item){
            $snapshot[$slot] = [
                'id' => $item->getTypeId(),
                'count' => $item->getCount(),
                'nbt' => $item->hasNamedTag() ? clone $item->getNamedTag() : null
            ];
        }

        self::$playerInventoryState[$name] = [
            'snapshot' => $snapshot,
            'time' => microtime(true)
        ];
    }
    public static function validateTransaction(Player $player, array $actions): bool{
        $name = $player->getName();

        if(!isset(self::$playerInventoryState[$name])){
            self::snapshotInventory($player);
            return true;
        }
        $state = self::$playerInventoryState[$name];
        $snapshot = $state['snapshot'];
        $inventory = $player->getInventory();
        foreach($actions as $action){
            if(!isset($action['slot'], $action['oldItem'], $action['newItem'])){
                continue;
            }
            $slot = $action['slot'];

            if(!isset($snapshot[$slot])){
                if($action['oldItem']['id'] !== 0){
                    return false;
                }
                continue;
            }
            $expected = $snapshot[$slot];
            $oldItem = $action['oldItem'];
            if($expected['id'] !== $oldItem['id'] || $expected['count'] !== $oldItem['count']){
                return false;
            }
        }
        return true;
    }
    public static function rollbackTransaction(Player $player): void{
        $name = $player->getName();

        if(!isset(self::$playerInventoryState[$name])){
            return;
        }
        $snapshot = self::$playerInventoryState[$name]['snapshot'];
        $inventory = $player->getInventory();
        foreach($snapshot as $slot => $data){
            $item = Item::nbtDeserialize($data['nbt'] ?? new \pocketmine\nbt\tag\CompoundTag());
            $item->setCount($data['count']);
            $inventory->setItem($slot, $item);
        }
    }
    public static function beginTransaction(Player $player, int $transactionId): void{
        $name = $player->getName();
        self::$pendingTransactions[$name] = [
            'id' => $transactionId,
            'time' => microtime(true)
        ];
        self::snapshotInventory($player);
    }
    public static function commitTransaction(Player $player): void{
        $name = $player->getName();
        unset(self::$pendingTransactions[$name]);
        self::snapshotInventory($player);
    }
    public static function hasPendingTransaction(Player $player): bool{
        $name = $player->getName();

        if(!isset(self::$pendingTransactions[$name])){
            return false;
        }
        $age = microtime(true) - self::$pendingTransactions[$name]['time'];
        if($age > 5.0){
            unset(self::$pendingTransactions[$name]);
            return false;
        }
        return true;
    }
    public static function cleanup(string $playerName): void{
        unset(self::$playerInventoryState[$playerName]);
        unset(self::$pendingTransactions[$playerName]);
    }
    public static function cleanupExpired(): int{
        $removed = 0;
        $now = microtime(true);
        foreach(self::$playerInventoryState as $name => $state){
            if(($now - $state['time']) > 300.0){
                unset(self::$playerInventoryState[$name]);
                $removed++;
            }
        }
        foreach(self::$pendingTransactions as $name => $tx){
            if(($now - $tx['time']) > 5.0){
                unset(self::$pendingTransactions[$name]);
                $removed++;
            }
        }
        return $removed;
    }
}
