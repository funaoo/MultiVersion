<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Static;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
class ItemTranslator{
    private static array $validItems = [];

    private static array $itemFallbacks = [];

    private static array $unsupportedNBT = [];
    public static function register(int $protocol, array $itemTable): void{
        self::$validItems[$protocol] = [];

        foreach($itemTable as $itemData){
            $name = $itemData['name'];
            self::$validItems[$protocol][$name] = $itemData;
        }

        self::registerFallbacks($protocol);
        self::registerUnsupportedNBT($protocol);
    }
    private static function registerFallbacks(int $protocol): void{
        self::$itemFallbacks[$protocol] = [
            'minecraft:mace' => 'minecraft:iron_sword',
            'minecraft:wind_charge' => 'minecraft:snowball',
            'minecraft:breeze_rod' => 'minecraft:blaze_rod',
            'minecraft:trial_key' => 'minecraft:gold_ingot',
            'minecraft:ominous_trial_key' => 'minecraft:gold_ingot',

            'minecraft:copper_ingot' => 'minecraft:iron_ingot',
            'minecraft:raw_copper' => 'minecraft:iron_ore',

            'minecraft:echo_shard' => 'minecraft:diamond',
            'minecraft:recovery_compass' => 'minecraft:compass',

            'minecraft:cherry_log' => 'minecraft:oak_log',
            'minecraft:mangrove_log' => 'minecraft:oak_log',
            'minecraft:bamboo_planks' => 'minecraft:oak_planks',
        ];
    }
    private static function registerUnsupportedNBT(int $protocol): void{
        if($protocol <= 527){
            self::$unsupportedNBT[$protocol] = [
                'minecraft:can_place_on',
                'minecraft:can_destroy',
                'minecraft:item_lock',
                'minecraft:keep_on_death',
                'minecraft:trim',
                'minecraft:pot_decorations',
                'minecraft:consumable',
                'minecraft:use_animation',
            ];
        }elseif($protocol <= 594){
            self::$unsupportedNBT[$protocol] = [
                'minecraft:consumable',
                'minecraft:use_animation',
            ];
        }else{
            self::$unsupportedNBT[$protocol] = [];
        }
    }
    public static function translateItem(int $protocol, Item $item): Item{
        $itemName = $item->getVanillaName();

        if(!self::itemExists($protocol, $itemName)){
            $item = self::downgradeItem($protocol, $item);
        }

        $item = self::stripUnsupportedNBT($protocol, $item);

        if(!self::isItemSafe($protocol, $item)){
            return VanillaItems::STONE();
        }

        return $item;
    }
    private static function itemExists(int $protocol, string $itemName): bool{
        return isset(self::$validItems[$protocol][$itemName]);
    }
    private static function downgradeItem(int $protocol, Item $item): Item{
        $itemName = $item->getVanillaName();

        if(isset(self::$itemFallbacks[$protocol][$itemName])){
            $fallbackName = self::$itemFallbacks[$protocol][$itemName];
            return self::createItemFromName($fallbackName, $item->getCount());
        }

        if(str_contains($itemName, 'sword')){
            return VanillaItems::IRON_SWORD()->setCount($item->getCount());
        }
        if(str_contains($itemName, 'pickaxe')){
            return VanillaItems::IRON_PICKAXE()->setCount($item->getCount());
        }
        if(str_contains($itemName, 'axe')){
            return VanillaItems::IRON_AXE()->setCount($item->getCount());
        }
        if(str_contains($itemName, 'shovel')){
            return VanillaItems::IRON_SHOVEL()->setCount($item->getCount());
        }
        if(str_contains($itemName, 'hoe')){
            return VanillaItems::IRON_HOE()->setCount($item->getCount());
        }

        if(str_contains($itemName, 'food') || str_contains($itemName, 'stew')){
            return VanillaItems::APPLE()->setCount($item->getCount());
        }

        return VanillaItems::STONE()->setCount($item->getCount());
    }
    private static function createItemFromName(string $name, int $count = 1): Item{
        $mapping = [
            'minecraft:stone' => VanillaItems::STONE(),
            'minecraft:iron_sword' => VanillaItems::IRON_SWORD(),
            'minecraft:iron_ingot' => VanillaItems::IRON_INGOT(),
            'minecraft:gold_ingot' => VanillaItems::GOLD_INGOT(),
            'minecraft:diamond' => VanillaItems::DIAMOND(),
            'minecraft:oak_log' => VanillaItems::OAK_LOG(),
            'minecraft:oak_planks' => VanillaItems::OAK_PLANKS(),
        ];

        $item = $mapping[$name] ?? VanillaItems::STONE();
        return $item->setCount($count);
    }
    private static function stripUnsupportedNBT(int $protocol, Item $item): Item{
        if(!$item->hasNamedTag()){
            return $item;
        }

        $nbt = $item->getNamedTag();
        $modified = false;

        $unsupported = self::$unsupportedNBT[$protocol] ?? [];

        foreach($unsupported as $tag){
            if($nbt->getTag($tag) !== null){
                $nbt->removeTag($tag);
                $modified = true;
            }
        }

        if($nbt->getTag('display') !== null){
            $display = $nbt->getCompoundTag('display');
            if($display !== null && $display->getTag('Name') !== null){
                $name = $display->getString('Name');
                if(str_contains($name, '§') && strlen($name) > 100){
                    $display->removeTag('Name');
                    $modified = true;
                }
            }
        }

        if($modified){
            $item = clone $item;
            $item->setNamedTag($nbt);
        }

        return $item;
    }
    private static function isItemSafe(int $protocol, Item $item): bool{
        $itemName = $item->getVanillaName();
        if(!self::itemExists($protocol, $itemName)){
            return false;
        }

        if($item->hasNamedTag()){
            $nbt = $item->getNamedTag();
            $unsupported = self::$unsupportedNBT[$protocol] ?? [];

            foreach($unsupported as $tag){
                if($nbt->getTag($tag) !== null){
                    return false;
                }
            }
        }

        return true;
    }
    public static function getStats(int $protocol): array{
        return [
            'valid_items' => count(self::$validItems[$protocol] ?? []),
            'fallbacks' => count(self::$itemFallbacks[$protocol] ?? []),
            'unsupported_nbt' => count(self::$unsupportedNBT[$protocol] ?? [])
        ];
    }
}
