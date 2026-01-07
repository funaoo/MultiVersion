<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Metadata;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
final class EntityMetadataMapper{
    private static ?self $instance = null;
    private array $mappings = [];
    private function __construct(){}
    public static function getInstance(): self{
        if(self::$instance === null){
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function getMapping(int $protocol): MetadataMapping{
        if(!isset($this->mappings[$protocol])){
            $this->mappings[$protocol] = $this->buildMapping($protocol);
        }
        return $this->mappings[$protocol];
    }
    private function buildMapping(int $protocol): MetadataMapping{
        $map = [];

        foreach($this->getAllMetadataKeys() as $key){
            $map[$key] = $this->translateKey($key, $protocol);
        }
        return new MetadataMapping($protocol, $map);
    }
    private function getAllMetadataKeys(): array{
        return [
            EntityMetadataProperties::FLAGS,
            EntityMetadataProperties::HEALTH,
            EntityMetadataProperties::VARIANT,
            EntityMetadataProperties::COLOR,
            EntityMetadataProperties::NAMETAG,
            EntityMetadataProperties::OWNER_EID,
            EntityMetadataProperties::TARGET_EID,
            EntityMetadataProperties::AIR,
            EntityMetadataProperties::POTION_COLOR,
            EntityMetadataProperties::POTION_AMBIENT,
            EntityMetadataProperties::JUMP_DURATION,
            EntityMetadataProperties::HURT_TIME,
            EntityMetadataProperties::HURT_DIRECTION,
            EntityMetadataProperties::PADDLE_TIME_LEFT,
            EntityMetadataProperties::PADDLE_TIME_RIGHT,
            EntityMetadataProperties::EXPERIENCE_VALUE,
            EntityMetadataProperties::MINECART_DISPLAY_BLOCK,
            EntityMetadataProperties::MINECART_DISPLAY_OFFSET,
            EntityMetadataProperties::MINECART_HAS_DISPLAY,
            EntityMetadataProperties::ENDERMAN_HELD_ITEM_ID,
            EntityMetadataProperties::ENTITY_AGE,
            EntityMetadataProperties::PLAYER_FLAGS,
            EntityMetadataProperties::PLAYER_INDEX,
            EntityMetadataProperties::PLAYER_BED_POSITION,
            EntityMetadataProperties::FIREBALL_POWER_X,
            EntityMetadataProperties::FIREBALL_POWER_Y,
            EntityMetadataProperties::FIREBALL_POWER_Z,
            EntityMetadataProperties::POTION_AUX_VALUE,
            EntityMetadataProperties::LEAD_HOLDER_EID,
            EntityMetadataProperties::SCALE,
            EntityMetadataProperties::INTERACTIVE_TAG,
            EntityMetadataProperties::NPC_SKIN_ID,
            EntityMetadataProperties::URL_TAG,
            EntityMetadataProperties::MAX_AIR,
            EntityMetadataProperties::MARK_VARIANT,
            EntityMetadataProperties::CONTAINER_TYPE,
            EntityMetadataProperties::CONTAINER_BASE_SIZE,
            EntityMetadataProperties::CONTAINER_EXTRA_SLOTS_PER_STRENGTH,
            EntityMetadataProperties::BLOCK_TARGET,
            EntityMetadataProperties::WITHER_INVULNERABLE_TICKS,
            EntityMetadataProperties::WITHER_TARGET_1,
            EntityMetadataProperties::WITHER_TARGET_2,
            EntityMetadataProperties::WITHER_TARGET_3,
            EntityMetadataProperties::AERIAL_ATTACK,
            EntityMetadataProperties::BOUNDINGBOX_WIDTH,
            EntityMetadataProperties::BOUNDINGBOX_HEIGHT,
            EntityMetadataProperties::FUSE_LENGTH,
            EntityMetadataProperties::RIDER_SEAT_POSITION,
            EntityMetadataProperties::RIDER_ROTATION_LOCKED,
            EntityMetadataProperties::RIDER_MAX_ROTATION,
            EntityMetadataProperties::RIDER_MIN_ROTATION,
            EntityMetadataProperties::RIDER_ROTATION_OFFSET,
            EntityMetadataProperties::AREA_EFFECT_CLOUD_RADIUS,
            EntityMetadataProperties::AREA_EFFECT_CLOUD_WAITING,
            EntityMetadataProperties::AREA_EFFECT_CLOUD_PARTICLE_ID,
            EntityMetadataProperties::SHULKER_PEAK_ID,
            EntityMetadataProperties::SHULKER_ATTACH_FACE,
            EntityMetadataProperties::SHULKER_ATTACHED,
            EntityMetadataProperties::SHULKER_ATTACH_POS,
            EntityMetadataProperties::TRADING_PLAYER_EID,
            EntityMetadataProperties::TRADING_CAREER,
            EntityMetadataProperties::HAS_COMMAND_BLOCK,
            EntityMetadataProperties::COMMAND_BLOCK_COMMAND,
            EntityMetadataProperties::COMMAND_BLOCK_LAST_OUTPUT,
            EntityMetadataProperties::COMMAND_BLOCK_TRACK_OUTPUT,
            EntityMetadataProperties::CONTROLLING_RIDER_SEAT_NUMBER,
            EntityMetadataProperties::STRENGTH,
            EntityMetadataProperties::MAX_STRENGTH,
            EntityMetadataProperties::SPELL_CASTING_COLOR,
            EntityMetadataProperties::LIMITED_LIFE,
            EntityMetadataProperties::ARMOR_STAND_POSE_INDEX,
            EntityMetadataProperties::ENDER_CRYSTAL_TIME_OFFSET,
            EntityMetadataProperties::ALWAYS_SHOW_NAMETAG,
            EntityMetadataProperties::COLOR_2,
            EntityMetadataProperties::SCORE_TAG,
            EntityMetadataProperties::BALLOON_ATTACHED_ENTITY,
            EntityMetadataProperties::PUFFERFISH_SIZE,
            EntityMetadataProperties::BOAT_BUBBLE_TIME,
            EntityMetadataProperties::PLAYER_AGENT_EID,
            EntityMetadataProperties::EATING_COUNTER,
            EntityMetadataProperties::FLAGS_EXTENDED,
            EntityMetadataProperties::LAYING_AMOUNT,
            EntityMetadataProperties::LAYING_DIRECTION,
            EntityMetadataProperties::DURATION,
            EntityMetadataProperties::SPAWN_TIME,
            EntityMetadataProperties::CHANGE_RATE,
            EntityMetadataProperties::CHANGE_ON_PICKUP,
            EntityMetadataProperties::PICKUP_COUNT,
            EntityMetadataProperties::INTERACT_TEXT,
            EntityMetadataProperties::TRADE_TIER,
            EntityMetadataProperties::MAX_TRADE_TIER,
            EntityMetadataProperties::TRADE_EXPERIENCE,
            EntityMetadataProperties::SKIN_ID,
            EntityMetadataProperties::SPAWNING_FRAMES,
            EntityMetadataProperties::COMMAND_BLOCK_TICK_DELAY,
            EntityMetadataProperties::COMMAND_BLOCK_EXECUTE_ON_FIRST_TICK,
            EntityMetadataProperties::AMBIENT_SOUND_INTERVAL,
            EntityMetadataProperties::AMBIENT_SOUND_INTERVAL_RANGE,
            EntityMetadataProperties::AMBIENT_SOUND_EVENT_NAME,
            EntityMetadataProperties::FALL_DAMAGE_MULTIPLIER,
            EntityMetadataProperties::NAME_RAW_TEXT,
            EntityMetadataProperties::CAN_RIDE_TARGET,
            EntityMetadataProperties::LOW_TIER_CURED_DISCOUNT,
            EntityMetadataProperties::HIGH_TIER_CURED_DISCOUNT,
            EntityMetadataProperties::NEARBY_CURED_DISCOUNT,
            EntityMetadataProperties::NEARBY_CURED_DISCOUNT_TIMESTAMP,
            EntityMetadataProperties::HITBOX,
            EntityMetadataProperties::IS_BUOYANT,
            EntityMetadataProperties::BASE_RUNTIME_ID,
            EntityMetadataProperties::FREEZING_EFFECT_STRENGTH,
            EntityMetadataProperties::BUOYANCY_DATA,
            EntityMetadataProperties::GOAT_HORN_COUNT,
            EntityMetadataProperties::UPDATE_PROPERTIES,
        ];
    }
    private function translateKey(int $key, int $protocol): int{
        return $key;
    }
}
