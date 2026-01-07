<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Static;
final class BlockFallbackRegistry{
    private static array $solidityGroups = [
        'solid' => ['stone', 'cobblestone', 'planks', 'dirt', 'grass_block', 'log', 'wood'],
        'transparent' => ['glass', 'glass_pane', 'ice', 'slime', 'honey_block'],
        'liquid' => ['water', 'lava'],
        'plant' => ['grass', 'fern', 'flower', 'sapling', 'leaves'],
        'light' => ['torch', 'lantern', 'glowstone', 'sea_lantern'],
    ];
    private static array $materialGroups = [
        'wood' => ['oak', 'spruce', 'birch', 'jungle', 'acacia', 'dark_oak', 'mangrove', 'cherry', 'bamboo'],
        'stone' => ['stone', 'cobblestone', 'deepslate', 'blackstone', 'tuff'],
        'metal' => ['iron', 'gold', 'copper', 'netherite'],
    ];
    private static array $categoryFallbacks = [
        'log' => 'oak_log',
        'planks' => 'oak_planks',
        'leaves' => 'oak_leaves',
        'sapling' => 'oak_sapling',
        'slab' => 'stone_slab',
        'stairs' => 'stone_stairs',
        'fence' => 'oak_fence',
        'door' => 'oak_door',
        'sign' => 'oak_sign',
        'button' => 'stone_button',
        'pressure_plate' => 'stone_pressure_plate',
    ];
    private static array $specificFallbacks = [
        'cherry_log' => 'oak_log',
        'cherry_planks' => 'oak_planks',
        'cherry_leaves' => 'oak_leaves',
        'mangrove_log' => 'oak_log',
        'mangrove_planks' => 'oak_planks',
        'mangrove_leaves' => 'oak_leaves',
        'bamboo_planks' => 'oak_planks',
        'bamboo_mosaic' => 'oak_planks',
        'deepslate' => 'stone',
        'deepslate_iron_ore' => 'iron_ore',
        'deepslate_gold_ore' => 'gold_ore',
        'deepslate_diamond_ore' => 'diamond_ore',
        'deepslate_coal_ore' => 'coal_ore',
        'tuff' => 'stone',
        'calcite' => 'stone',
        'dripstone_block' => 'stone',
        'pointed_dripstone' => 'stone',
        'copper_ore' => 'iron_ore',
        'raw_copper_block' => 'iron_block',
        'copper_block' => 'iron_block',
        'exposed_copper' => 'iron_block',
        'weathered_copper' => 'iron_block',
        'oxidized_copper' => 'iron_block',
        'sculk' => 'black_wool',
        'sculk_sensor' => 'observer',
        'sculk_catalyst' => 'observer',
        'sculk_shrieker' => 'observer',
        'mud' => 'dirt',
        'muddy_mangrove_roots' => 'dirt',
        'packed_mud' => 'dirt',
        'mud_bricks' => 'brick_block',
        'reinforced_deepslate' => 'obsidian',
        'suspicious_sand' => 'sand',
        'suspicious_gravel' => 'gravel',
        'calibrated_sculk_sensor' => 'observer',
        'chiseled_copper' => 'cut_copper',
        'copper_grate' => 'iron_bars',
        'tuff_stairs' => 'stone_stairs',
        'tuff_slab' => 'stone_slab',
        'polished_tuff' => 'polished_andesite',
    ];
    public static function findFallback(string $blockName): string{
        $cleanName = str_replace('minecraft:', '', $blockName);
        if(isset(self::$specificFallbacks[$cleanName])){
            return 'minecraft:' . self::$specificFallbacks[$cleanName];
        }
        foreach(self::$categoryFallbacks as $suffix => $fallback){
            if(str_ends_with($cleanName, '_' . $suffix)){
                return 'minecraft:' . $fallback;
            }
        }
        foreach(self::$materialGroups['wood'] as $wood){
            if(str_contains($cleanName, $wood)){
                if(str_contains($cleanName, 'log')){
                    return 'minecraft:oak_log';
                }
                if(str_contains($cleanName, 'planks')){
                    return 'minecraft:oak_planks';
                }
                if(str_contains($cleanName, 'leaves')){
                    return 'minecraft:oak_leaves';
                }
            }
        }
        foreach(self::$materialGroups['stone'] as $stone){
            if(str_contains($cleanName, $stone)){
                return 'minecraft:stone';
            }
        }
        if(str_contains($cleanName, 'ore')){
            return 'minecraft:stone';
        }
        if(str_contains($cleanName, 'wool') || str_contains($cleanName, 'carpet')){
            return 'minecraft:white_wool';
        }
        if(str_contains($cleanName, 'concrete') || str_contains($cleanName, 'terracotta')){
            return 'minecraft:stone';
        }
        if(str_contains($cleanName, 'glass')){
            return 'minecraft:glass';
        }
        return 'minecraft:stone';
    }
    public static function getSolidityGroup(string $blockName): string{
        $cleanName = str_replace('minecraft:', '', $blockName);
        foreach(self::$solidityGroups as $group => $blocks){
            foreach($blocks as $block){
                if(str_contains($cleanName, $block)){
                    return $group;
                }
            }
        }
        return 'solid';
    }
    public static function isTransparent(string $blockName): bool{
        $group = self::getSolidityGroup($blockName);
        return in_array($group, ['transparent', 'plant', 'light'], true);
    }
    public static function isSolid(string $blockName): bool{
        return self::getSolidityGroup($blockName) === 'solid';
    }
}
