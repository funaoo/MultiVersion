<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Packets;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\types\PlayerAction;
final class PlayerActionPacketHandler{
    private const ACTION_MAPPINGS = [
        'START_BREAK' => 0,
        'ABORT_BREAK' => 1,
        'STOP_BREAK' => 2,
        'GET_UPDATED_BLOCK' => 3,
        'DROP_ITEM' => 4,
        'START_SLEEPING' => 5,
        'STOP_SLEEPING' => 6,
        'RESPAWN' => 7,
        'JUMP' => 8,
        'START_SPRINT' => 9,
        'STOP_SPRINT' => 10,
        'START_SNEAK' => 11,
        'STOP_SNEAK' => 12,
        'CREATIVE_PLAYER_DESTROY_BLOCK' => 13,
        'DIMENSION_CHANGE_ACK' => 14,
        'START_GLIDE' => 15,
        'STOP_GLIDE' => 16,
        'BUILD_DENIED' => 17,
        'CRACK_BREAK' => 18,
        'CHANGE_SKIN' => 19,
        'SET_ENCHANTMENT_SEED' => 20,
        'START_SWIMMING' => 21,
        'STOP_SWIMMING' => 22,
        'START_SPIN_ATTACK' => 23,
        'STOP_SPIN_ATTACK' => 24,
        'INTERACT_BLOCK' => 25,
    ];
    public static function getActionId(string $actionName): int{
        if(defined('pocketmine\network\mcpe\protocol\types\PlayerAction::' . $actionName)){
            return constant('pocketmine\network\mcpe\protocol\types\PlayerAction::' . $actionName);
        }

        if(defined('pocketmine\network\mcpe\protocol\PlayerActionPacket::ACTION_' . $actionName)){
            return constant('pocketmine\network\mcpe\protocol\PlayerActionPacket::ACTION_' . $actionName);
        }

        return self::ACTION_MAPPINGS[$actionName] ?? -1;
    }
    public static function getActionName(int $actionId): string{
        $flipped = array_flip(self::ACTION_MAPPINGS);
        return $flipped[$actionId] ?? 'UNKNOWN';
    }
    public static function isActionSupported(int $actionId, int $protocol): bool{
        $versionRequirements = [
            21 => 527,
            22 => 527,
            23 => 527,
            24 => 527,
            25 => 594,
        ];
        $required = $versionRequirements[$actionId] ?? 0;
        return $protocol >= $required;
    }
    public static function translatePacket(PlayerActionPacket $packet, int $targetProtocol): ?PlayerActionPacket{
        $action = $packet->action;

        if(!self::isActionSupported($action, $targetProtocol)){
            return null;
        }
        return $packet;
    }
    public static function safeGetAction(string $name): int{
        try{
            return self::getActionId($name);
        }catch(\Throwable $e){
            return -1;
        }
    }
}
