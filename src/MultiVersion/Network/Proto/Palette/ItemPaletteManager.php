<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Palette;
use pocketmine\item\ItemTypeIds;
use pocketmine\network\mcpe\convert\GlobalItemTypeDictionary;
final class ItemPaletteManager{
    private static ?self $instance = null;
    private array $palettes = [];
    private function __construct(){}
    public static function getInstance(): self{
        if(self::$instance === null){
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function getPalette(int $protocol): ItemPalette{
        if(!isset($this->palettes[$protocol])){
            $this->palettes[$protocol] = $this->buildPalette($protocol);
        }
        return $this->palettes[$protocol];
    }
    private function buildPalette(int $protocol): ItemPalette{
        $mapping = [];
        $reverseMapping = [];
        foreach(ItemTypeIds::getAll() as $typeId){
            $networkId = $this->getNetworkId($typeId, $protocol);
            $mapping[$typeId] = $networkId;
            $reverseMapping[$networkId] = $typeId;
        }
        return new ItemPalette($protocol, $mapping, $reverseMapping);
    }
    private function getNetworkId(int $typeId, int $protocol): int{
        return $typeId;
    }
}
