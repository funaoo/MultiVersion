<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\v594;
use MultiVersion\Network\Proto\TypeConverter;
use MultiVersion\Network\Proto\Static\RuntimeBlockMapping;
use MultiVersion\Network\Proto\Static\RuntimeItemMapping;
use pocketmine\item\Item;
class v594TypeConverter extends TypeConverter{
    private array $missingBlocks = [
        'minecraft:crafter' => true,
        'minecraft:trial_spawner' => true,
        'minecraft:vault' => true,
    ];
    private array $missingItems = [
        'minecraft:mace' => true,
        'minecraft:wind_charge' => true,
        'minecraft:breeze_rod' => true,
    ];
    public function blockRuntimeIdToClient(int $serverRuntimeId): int{
        return RuntimeBlockMapping::serverToClient(594, $serverRuntimeId);
    }
    public function blockRuntimeIdToServer(int $clientRuntimeId): int{
        return RuntimeBlockMapping::clientToServer(594, $clientRuntimeId);
    }
    public function itemIdToClient(int $serverItemId): int{
        return RuntimeItemMapping::serverToClient(594, $serverItemId);
    }
    public function itemIdToServer(int $clientItemId): int{
        return RuntimeItemMapping::clientToServer(594, $clientItemId);
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

        $unsupported = [
            'minecraft:consumable',
            'minecraft:use_animation',
        ];

        foreach($unsupported as $tag){
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
        return !isset($this->missingBlocks[$blockName]);
    }
    public function hasItem(string $itemName): bool{
        return !isset($this->missingItems[$itemName]);
    }
}
