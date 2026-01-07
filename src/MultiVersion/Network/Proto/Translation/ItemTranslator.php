<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Translation;
use MultiVersion\Network\Proto\Palette\ItemPalette;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
final class ItemTranslator{
    public static function translateItemStack(ItemStack $item, ItemPalette $palette): ItemStack{
        if($item->getId() === 0){
            return $item;
        }
        $newId = $palette->toNetworkId($item->getId());

        return new ItemStack(
            $newId,
            $item->getMeta(),
            $item->getCount(),
            $item->getBlockRuntimeId(),
            $item->getNbt()
        );
    }
    public static function translateItemStackWrapper(ItemStackWrapper $wrapper, ItemPalette $palette): ItemStackWrapper{
        $stack = $wrapper->getItemStack();
        $translated = self::translateItemStack($stack, $palette);

        return new ItemStackWrapper($wrapper->getStackId(), $translated);
    }
    public static function translateItemArray(array $items, ItemPalette $palette): array{
        $result = [];

        foreach($items as $item){
            if($item instanceof ItemStackWrapper){
                $result[] = self::translateItemStackWrapper($item, $palette);
            }elseif($item instanceof ItemStack){
                $result[] = self::translateItemStack($item, $palette);
            }else{
                $result[] = $item;
            }
        }

        return $result;
    }
}
