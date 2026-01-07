<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Packets;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\TreeRoot;
class CustomStartGamePacket extends StartGamePacket{
    private array $customBlockPalette = [];
    private array $customItemTable = [];
    public static function fromOriginal(StartGamePacket $original): self{
        $new = new self();

        $new->actorUniqueId = $original->actorUniqueId;
        $new->actorRuntimeId = $original->actorRuntimeId;
        $new->playerGamemode = $original->playerGamemode;
        $new->playerPosition = $original->playerPosition;
        $new->pitch = $original->pitch;
        $new->yaw = $original->yaw;
        $new->seed = $original->seed;
        $new->spawnSettings = $original->spawnSettings;
        $new->generator = $original->generator;
        $new->worldGamemode = $original->worldGamemode;
        $new->difficulty = $original->difficulty;
        $new->spawnPosition = $original->spawnPosition;
        $new->hasAchievementsDisabled = $original->hasAchievementsDisabled;
        $new->time = $original->time;
        $new->eduEditorWorldType = $original->eduEditorWorldType;
        $new->hasEduFeaturesEnabled = $original->hasEduFeaturesEnabled;
        $new->eduResourceUri = $original->eduResourceUri;
        $new->hasExperimentalGameplayEnabled = $original->hasExperimentalGameplayEnabled;
        $new->levelId = $original->levelId;
        $new->worldName = $original->worldName;
        $new->premiumWorldTemplateId = $original->premiumWorldTemplateId;
        $new->isTrial = $original->isTrial;
        $new->playerMovementSettings = $original->playerMovementSettings;
        $new->currentTick = $original->currentTick;
        $new->enchantmentSeed = $original->enchantmentSeed;
        $new->multiplayerCorrelationId = $original->multiplayerCorrelationId;
        $new->enableNewInventorySystem = $original->enableNewInventorySystem;
        $new->serverSoftwareVersion = $original->serverSoftwareVersion;
        $new->playerActorProperties = $original->playerActorProperties;
        $new->blockPaletteChecksum = $original->blockPaletteChecksum;
        $new->worldTemplateId = $original->worldTemplateId;
        $new->enableClientSideGeneration = $original->enableClientSideGeneration;

        return $new;
    }
    public function setCustomBlockPalette(array $palette): void{
        $this->customBlockPalette = $palette;
    }
    public function setCustomItemTable(array $table): void{
        $this->customItemTable = $table;
    }
    protected function encodePayload(PacketSerializer $out): void{
        $out->putActorUniqueId($this->actorUniqueId);
        $out->putActorRuntimeId($this->actorRuntimeId);
        $out->putVarInt($this->playerGamemode);
        $out->putVector3($this->playerPosition);
        $out->putLFloat($this->pitch);
        $out->putLFloat($this->yaw);

        $out->putLLong($this->seed);
        $out->putLShort($this->spawnSettings->getBiomeType());
        $out->putString($this->spawnSettings->getBiomeName());
        $out->putVarInt($this->spawnSettings->getDimension());

        $out->putVarInt($this->generator);
        $out->putVarInt($this->worldGamemode);
        $out->putVarInt($this->difficulty);
        $out->putBlockPosition($this->spawnPosition);
        $out->putBool($this->hasAchievementsDisabled);

        $out->putVarInt($this->time);
        $out->putVarInt($this->eduEditorWorldType);
        $out->putBool($this->hasEduFeaturesEnabled);
        $out->putString($this->eduResourceUri);

        $out->putBool($this->hasExperimentalGameplayEnabled);
        $out->putBool($this->enableNewInventorySystem);

        $out->putString($this->levelId);
        $out->putString($this->worldName);
        $out->putString($this->premiumWorldTemplateId);
        $out->putBool($this->isTrial);

        $this->playerMovementSettings->write($out);

        $out->putLLong($this->currentTick);
        $out->putVarInt($this->enchantmentSeed);

        if(!empty($this->customBlockPalette)){
            $this->writeCustomBlockPalette($out);
        }else{
            $out->put(new NetworkLittleEndianNBTStream()->write(new TreeRoot(CompoundTag::create())));
        }

        if(!empty($this->customItemTable)){
            $this->writeCustomItemTable($out);
        }else{
            $out->putUnsignedVarInt(0);
        }

        $out->putString($this->multiplayerCorrelationId);
        $out->putBool($this->enableClientSideGeneration);
        $out->putBool(false);

        $this->playerActorProperties->write($out);

        $out->putLLong($this->blockPaletteChecksum);
        $out->put($this->worldTemplateId->toBinary());
        $out->putBool(false);
        $out->putString($this->serverSoftwareVersion);
    }
    private function writeCustomBlockPalette(PacketSerializer $out): void{
        $root = CompoundTag::create();
        $blocks = new ListTag();

        foreach($this->customBlockPalette as $blockData){
            $blockTag = CompoundTag::create()
                ->setString("name", $blockData['name']);

            if(isset($blockData['states']) && !empty($blockData['states'])){
                $statesTag = CompoundTag::create();
                foreach($blockData['states'] as $key => $value){
                    if(is_int($value)){
                        $statesTag->setInt($key, $value);
                    }elseif(is_string($value)){
                        $statesTag->setString($key, $value);
                    }elseif(is_bool($value)){
                        $statesTag->setByte($key, $value ? 1 : 0);
                    }
                }
                $blockTag->setTag("states", $statesTag);
            }else{
                $blockTag->setTag("states", CompoundTag::create());
            }

            $blocks->push($blockTag);
        }

        $root->setTag("blocks", $blocks);
        $out->put((new NetworkLittleEndianNBTStream())->write(new TreeRoot($root)));
    }
    private function writeCustomItemTable(PacketSerializer $out): void{
        $out->putUnsignedVarInt(count($this->customItemTable));

        foreach($this->customItemTable as $item){
            $out->putString($item['name']);
            $out->putLShort($item['id']);
            $out->putBool($item['component_based'] ?? false);
        }
    }
}
