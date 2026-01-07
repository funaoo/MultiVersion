<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Metadata;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
final class MetadataMapping{
    private int $protocol;
    private array $map;
    public function __construct(int $protocol, array $map){
        $this->protocol = $protocol;
        $this->map = $map;
    }
    public function getProtocol(): int{
        return $this->protocol;
    }
    public function translate(EntityMetadataCollection $metadata): EntityMetadataCollection{
        $translated = new EntityMetadataCollection();
        foreach($metadata->getAll() as $key => $property){
            $newKey = $this->map[$key] ?? null;
            if($newKey !== null){
                $translated->set($newKey, $property);
            }
        }
        return $translated;
    }
    public function translateKey(int $key): ?int{
        return $this->map[$key] ?? null;
    }
}
