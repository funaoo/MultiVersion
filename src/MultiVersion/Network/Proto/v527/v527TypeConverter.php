<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\v527;
use MultiVersion\Network\Proto\TypeConverter;
use MultiVersion\Network\Proto\Static\RuntimeBlockMapping;
use MultiVersion\Network\Proto\Static\RuntimeItemMapping;
use pocketmine\item\Item;
class v527TypeConverter extends TypeConverter{
    public function blockRuntimeIdToClient(int $serverRuntimeId): int{
        return RuntimeBlockMapping::serverToClient(527, $serverRuntimeId);
    }
    public function blockRuntimeIdToServer(int $clientRuntimeId): int{
        return RuntimeBlockMapping::clientToServer(527, $clientRuntimeId);
    }
    public function itemIdToClient(int $serverItemId): int{
        return RuntimeItemMapping::serverToClient(527, $serverItemId);
    }
    public function itemIdToServer(int $clientItemId): int{
        return RuntimeItemMapping::clientToServer(527, $clientItemId);
    }
    public function entityIdToClient(int $serverEntityId): int{
        return $serverEntityId;
    }
    public function entityIdToServer(int $clientEntityId): int{
        return $clientEntityId;
    }
    public function stripUnsupportedItemTags(Item $item): Item{
        if(!$item->hasNamedTag()){
            return $item;
        }

        $nbt = $item->getNamedTag();
        $modified = false;

        $unsupportedTags = [
            'minecraft:can_place_on',
            'minecraft:can_destroy',
            'minecraft:item_lock',
            'minecraft:keep_on_death',
        ];

        foreach($unsupportedTags as $tag){
            if($nbt->getTag($tag) !== null){
                $nbt->removeTag($tag);
                $modified = true;
            }
        }

        if($modified){
            $item = clone $item;
            $item->setNamedTag($nbt);
        }

        return $item;
    }
    public function hasBlock(string $blockName): bool{
        return RuntimeBlockMapping::hasBlock(527, $blockName);
    }
    public function hasItem(string $itemName): bool{
        return RuntimeItemMapping::hasItem(527, $itemName);
    }
    public function getFallbackBlockId(): int{
        return 0;
    }
}
