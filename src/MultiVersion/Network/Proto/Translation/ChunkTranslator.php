<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Translation;
use MultiVersion\Network\Proto\Palette\BlockPalette;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;
final class ChunkTranslator{
    private const SUBCHUNK_VERSION = 9;
    private const PALETTE_VERSION = 1;
    public static function translateChunkData(string $data, BlockPalette $palette): string{
        if(strlen($data) < 1){
            return $data;
        }
        $offset = 0;
        $subchunkCount = ord($data[$offset++]);

        if($subchunkCount > 32){
            return $data;
        }
        $translatedSubchunks = '';

        for($i = 0; $i < $subchunkCount; $i++){
            if($offset >= strlen($data)){
                break;
            }

            $version = ord($data[$offset++]);

            if($version !== self::SUBCHUNK_VERSION){
                $subchunkData = self::readLegacySubchunk($data, $offset);
                $translatedSubchunks .= chr($version) . $subchunkData;
                continue;
            }

            $storageCount = ord($data[$offset++]);
            $yOffset = ord($data[$offset++]);

            $translatedSubchunks .= chr($version) . chr($storageCount) . chr($yOffset);

            for($s = 0; $s < $storageCount; $s++){
                $storage = self::readBlockStorage($data, $offset, $palette);
                $translatedSubchunks .= $storage;
            }
        }
        $biomeData = substr($data, $offset);

        return chr($subchunkCount) . $translatedSubchunks . $biomeData;
    }
    private static function readBlockStorage(string $data, int &$offset, BlockPalette $palette): string{
        $bitsPerBlock = ord($data[$offset++]) >> 1;
        $blocksPerWord = intdiv(32, $bitsPerBlock);
        $wordsPerBlock = intdiv(4096, $blocksPerWord);

        $blockData = substr($data, $offset, $wordsPerBlock * 4);
        $offset += $wordsPerBlock * 4;

        $paletteSize = unpack('V', substr($data, $offset, 4))[1];
        $offset += 4;

        $translatedPalette = [];

        for($i = 0; $i < $paletteSize; $i++){
            $nbtSize = unpack('V', substr($data, $offset, 4))[1];
            $offset += 4;

            $nbtData = substr($data, $offset, $nbtSize);
            $offset += $nbtSize;

            $translatedPalette[] = pack('V', $nbtSize) . $nbtData;
        }

        $result = chr($bitsPerBlock << 1);
        $result .= $blockData;
        $result .= pack('V', count($translatedPalette));

        foreach($translatedPalette as $entry){
            $result .= $entry;
        }

        return $result;
    }
    private static function readLegacySubchunk(string $data, int &$offset): string{
        $start = $offset;
        $storageCount = ord($data[$offset++]);

        for($i = 0; $i < $storageCount; $i++){
            $bitsPerBlock = ord($data[$offset++]) >> 1;
            $blocksPerWord = intdiv(32, $bitsPerBlock);
            $wordsPerBlock = intdiv(4096, $blocksPerWord);

            $offset += $wordsPerBlock * 4;

            $paletteSize = unpack('V', substr($data, $offset, 4))[1];
            $offset += 4;

            for($p = 0; $p < $paletteSize; $p++){
                $nbtSize = unpack('V', substr($data, $offset, 4))[1];
                $offset += 4;
                $offset += $nbtSize;
            }
        }

        return substr($data, $start, $offset - $start);
    }
}
