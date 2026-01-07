<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Latest;
use MultiVersion\Network\Proto\TypeConverter;
class LatestTypeConverter extends TypeConverter{
    public function blockRuntimeIdToClient(int $serverRuntimeId): int{
        return $serverRuntimeId;
    }
    public function blockRuntimeIdToServer(int $clientRuntimeId): int{
        return $clientRuntimeId;
    }
    public function itemIdToClient(int $serverItemId): int{
        return $serverItemId;
    }
    public function itemIdToServer(int $clientItemId): int{
        return $clientItemId;
    }
    public function entityIdToClient(int $serverEntityId): int{
        return $serverEntityId;
    }
    public function entityIdToServer(int $clientEntityId): int{
        return $clientEntityId;
    }
    public function hasBlock(string $blockName): bool{
        return true;
    }
    public function hasItem(string $itemName): bool{
        return true;
    }
}
