<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Palette;
use pocketmine\block\BlockTypeIds;
use pocketmine\network\mcpe\convert\GlobalItemTypeDictionary;
final class BlockPaletteManager{
    private static ?self $instance = null;
    private array $palettes = [];
    private function __construct(){}
    public static function getInstance(): self{
        if(self::$instance === null){
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function getPalette(int $protocol): BlockPalette{
        if(!isset($this->palettes[$protocol])){
            $this->palettes[$protocol] = $this->buildPalette($protocol);
        }
        return $this->palettes[$protocol];
    }
    private function buildPalette(int $protocol): BlockPalette{
        $mapping = [];
        $reverseMapping = [];
        foreach(BlockTypeIds::getAll() as $typeId){
            $runtimeId = $this->getRuntimeId($typeId, $protocol);
            $mapping[$typeId] = $runtimeId;
            $reverseMapping[$runtimeId] = $typeId;
        }
        return new BlockPalette($protocol, $mapping, $reverseMapping);
    }
    private function getRuntimeId(int $typeId, int $protocol): int{
        return $typeId;
    }
}
