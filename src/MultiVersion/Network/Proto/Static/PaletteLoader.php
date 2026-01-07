<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Static;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
class PaletteLoader{
    public static function loadBlockPalette(string $nbtPath): array{
        if(!file_exists($nbtPath)){
            throw new \RuntimeException("Block palette not found: $nbtPath");
        }

        $nbt = new BigEndianNbtSerializer();
        $data = file_get_contents($nbtPath);
        $offset = 0;

        try{
            $root = $nbt->read($data, $offset)->mustGetCompoundTag();
        }catch(\Exception $e){
            throw new \RuntimeException("Failed to parse block palette NBT: " . $e->getMessage());
        }

        $blocks = $root->getListTag("blocks");
        if($blocks === null){
            throw new \RuntimeException("Block palette missing 'blocks' tag");
        }

        $palette = [];

        foreach($blocks->getValue() as $blockTag){
            if(!$blockTag instanceof CompoundTag){
                continue;
            }

            $name = $blockTag->getString("name");
            $statesTag = $blockTag->getCompoundTag("states");

            $states = [];
            if($statesTag !== null){
                foreach($statesTag as $key => $tag){
                    $states[$key] = $tag->getValue();
                }
            }

            $palette[] = [
                'name' => $name,
                'states' => $states
            ];
        }

        return $palette;
    }
    public static function loadItemTable(string $jsonPath): array{
        if(!file_exists($jsonPath)){
            throw new \RuntimeException("Item table not found: $jsonPath");
        }

        $json = json_decode(file_get_contents($jsonPath), true);
        if(!is_array($json)){
            throw new \RuntimeException("Invalid item table JSON");
        }

        $items = [];

        foreach($json as $itemData){
            if(!isset($itemData['name']) || !isset($itemData['id'])){
                continue;
            }

            $items[] = [
                'name' => $itemData['name'],
                'id' => $itemData['id'],
                'component_based' => $itemData['component_based'] ?? false
            ];
        }

        return $items;
    }
    public static function createMinimalBlockPalette(): array{
        $blocks = [
            'air', 'stone', 'grass_block', 'dirt', 'cobblestone', 'planks',
            'bedrock', 'water', 'lava', 'sand', 'gravel', 'gold_ore',
            'iron_ore', 'coal_ore', 'oak_log', 'oak_leaves', 'glass',
            'diamond_ore', 'crafting_table', 'furnace', 'chest', 'torch',
            'oak_stairs', 'diamond_block', 'gold_block', 'iron_block'
        ];

        $palette = [];
        foreach($blocks as $block){
            $palette[] = [
                'name' => "minecraft:$block",
                'states' => []
            ];
        }

        return $palette;
    }
    public static function createMinimalItemTable(): array{
        $items = [
            'air' => 0, 'stone' => 1, 'grass_block' => 2, 'dirt' => 3,
            'cobblestone' => 4, 'planks' => 5, 'diamond' => 264,
            'iron_ingot' => 265, 'gold_ingot' => 266, 'stick' => 280
        ];

        $table = [];
        foreach($items as $name => $id){
            $table[] = [
                'name' => "minecraft:$name",
                'id' => $id,
                'component_based' => false
            ];
        }

        return $table;
    }
}
