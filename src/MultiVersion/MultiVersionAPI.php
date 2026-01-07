<?php
declare(strict_types=1);
namespace MultiVersion;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
final class MultiVersionAPI{
    private static array $playerProtocols = [];
    private static array $featureCache = [];
    private const FEATURE_MAP = [
        'deep_dark' => 560,
        'ancient_cities' => 560,
        'warden' => 560,
        'mangrove_swamp' => 560,
        'mud' => 560,
        'frogs' => 560,
        'allays' => 560,
        'trial_chambers' => 621,
        'crafter' => 621,
        'copper_family' => 527,
        'armadillo' => 621,
        'wolf_variants' => 594,
        'breeze' => 621,
        'decorated_pot' => 594,
        'cherry_grove' => 594,
        'bamboo_blocks' => 560,
        'hanging_signs' => 594,
        'chiseled_bookshelf' => 594,
        'camel' => 594,
        'sniffer' => 594,
        'smithing_templates' => 594,
    ];
    public static function registerPlayer(Player $player, int $protocol): void{
        self::$playerProtocols[$player->getName()] = $protocol;
    }
    public static function unregisterPlayer(Player $player): void{
        unset(self::$playerProtocols[$player->getName()]);
    }
    public static function getProtocol(Player $player): int{
        return self::$playerProtocols[$player->getName()] ?? ProtocolInfo::CURRENT_PROTOCOL;
    }
    public static function getClientVersion(Player $player): string{
        $protocol = self::getProtocol($player);

        return match(true){
            $protocol >= 630 => '1.20.70+',
            $protocol >= 621 => '1.20.50-1.20.60',
            $protocol >= 594 => '1.20.0-1.20.30',
            $protocol >= 560 => '1.19.50-1.19.80',
            $protocol >= 527 => '1.19.2-1.19.40',
            default => 'Unknown',
        };
    }
    public static function isLegacy(Player $player): bool{
        $protocol = self::getProtocol($player);
        return $protocol < ProtocolInfo::CURRENT_PROTOCOL;
    }
    public static function supportsFeature(Player $player, string $feature): bool{
        $cacheKey = $player->getName() . ':' . $feature;

        if(isset(self::$featureCache[$cacheKey])){
            return self::$featureCache[$cacheKey];
        }
        $protocol = self::getProtocol($player);
        $requiredProtocol = self::FEATURE_MAP[$feature] ?? PHP_INT_MAX;

        $result = $protocol >= $requiredProtocol;
        self::$featureCache[$cacheKey] = $result;

        return $result;
    }
    public static function getMinProtocol(): int{
        return 527;
    }
    public static function getMaxProtocol(): int{
        return ProtocolInfo::CURRENT_PROTOCOL;
    }
    public static function getSupportedProtocols(): array{
        return [527, 560, 594, 621, ProtocolInfo::CURRENT_PROTOCOL];
    }
    public static function isProtocolSupported(int $protocol): bool{
        return $protocol >= self::getMinProtocol() && $protocol <= self::getMaxProtocol();
    }
    public static function cleanup(string $playerName): void{
        unset(self::$playerProtocols[$playerName]);

        foreach(self::$featureCache as $key => $value){
            if(str_starts_with($key, $playerName . ':')){
                unset(self::$featureCache[$key]);
            }
        }
    }
}
