<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
abstract class TypeConverter{
    abstract public function blockRuntimeIdToClient(int $serverRuntimeId): int;
    abstract public function blockRuntimeIdToServer(int $clientRuntimeId): int;
    abstract public function itemIdToClient(int $serverItemId): int;
    abstract public function itemIdToServer(int $clientItemId): int;
    abstract public function entityIdToClient(int $serverEntityId): int;
    abstract public function entityIdToServer(int $clientEntityId): int;
    public function stripUnsupportedItemTags(Item $item): Item{
        return $item;
    }
    abstract public function hasBlock(string $blockName): bool;
    abstract public function hasItem(string $itemName): bool;
    public function getFallbackBlockId(): int{
        return 0;
    }
    public function getFallbackItemId(): int{
        return 0;
    }
}
