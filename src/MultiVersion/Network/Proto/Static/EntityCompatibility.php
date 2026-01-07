<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Static;
final class EntityCompatibility{
    private const ENTITY_DOWNGRADES = [
        527 => [
            'minecraft:warden' => 'minecraft:iron_golem',
            'minecraft:allay' => 'minecraft:vex',
            'minecraft:frog' => 'minecraft:rabbit',
            'minecraft:tadpole' => 'minecraft:tropical_fish',
            'minecraft:sniffer' => 'minecraft:ravager',
            'minecraft:camel' => 'minecraft:llama',
            'minecraft:armadillo' => 'minecraft:rabbit',
            'minecraft:breeze' => 'minecraft:vex',
            'minecraft:bogged' => 'minecraft:skeleton',
        ],
        594 => [
            'minecraft:sniffer' => 'minecraft:ravager',
            'minecraft:camel' => 'minecraft:llama',
            'minecraft:armadillo' => 'minecraft:rabbit',
            'minecraft:breeze' => 'minecraft:vex',
            'minecraft:bogged' => 'minecraft:skeleton',
        ],
        621 => [
            'minecraft:breeze' => 'minecraft:vex',
            'minecraft:bogged' => 'minecraft:skeleton',
        ],
    ];
    private const METADATA_FILTERS = [
        527 => [
            'variant' => ['sniffer', 'camel', 'armadillo'],
            'pose' => ['warden', 'frog'],
        ],
        594 => [
            'variant' => ['sniffer', 'camel', 'armadillo'],
        ],
    ];
    private const HITBOX_ADJUSTMENTS = [
        'minecraft:warden' => ['width' => 0.9, 'height' => 2.9],
        'minecraft:iron_golem' => ['width' => 1.4, 'height' => 2.7],
        'minecraft:allay' => ['width' => 0.35, 'height' => 0.6],
        'minecraft:vex' => ['width' => 0.4, 'height' => 0.8],
    ];
    public static function getEntityDowngrade(int $protocol, string $entityType): ?string{
        if(!isset(self::ENTITY_DOWNGRADES[$protocol])){
            return null;
        }
        return self::ENTITY_DOWNGRADES[$protocol][$entityType] ?? null;
    }
    public static function shouldFilterMetadata(int $protocol, string $entityType, string $metadataKey): bool{
        if(!isset(self::METADATA_FILTERS[$protocol])){
            return false;
        }
        foreach(self::METADATA_FILTERS[$protocol] as $key => $entities){
            if($key === $metadataKey && in_array($entityType, $entities, true)){
                return true;
            }
        }
        return false;
    }
    public static function normalizeMetadata(int $protocol, string $entityType, array $metadata): array{
        $normalized = [];
        foreach($metadata as $key => $value){
            if(!self::shouldFilterMetadata($protocol, $entityType, $key)){
                $normalized[$key] = $value;
            }
        }
        return $normalized;
    }
    public static function getHitboxAdjustment(string $entityType): ?array{
        return self::HITBOX_ADJUSTMENTS[$entityType] ?? null;
    }
    public static function isEntitySupported(int $protocol, string $entityType): bool{
        if($protocol >= 621){
            return true;
        }
        if(isset(self::ENTITY_DOWNGRADES[$protocol][$entityType])){
            return false;
        }
        return true;
    }
    public static function shouldDespawnEntity(int $protocol, string $entityType): bool{
        if(self::isEntitySupported($protocol, $entityType)){
            return false;
        }
        $downgrade = self::getEntityDowngrade($protocol, $entityType);
        return $downgrade === null;
    }
}
