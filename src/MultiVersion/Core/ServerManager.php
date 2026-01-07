<?php
declare(strict_types=1);
namespace MultiVersion\Core;
use MultiVersion\MultiVersion;
final class ServerManager{
    private MultiVersion $plugin;
    private int $totalConnections = 0;
    private int $packetsTranslated = 0;
    private int $cacheHits = 0;
    private int $translationErrors = 0;
    private int $serverProtocol = 621;
    public function __construct(MultiVersion $plugin){
        $this->plugin = $plugin;
        $this->loadStatistics();
    }
    private function loadStatistics(): void{
        $file = $this->plugin->getDataFolder() . "statistics.json";
        if(file_exists($file)){
            $data = json_decode(file_get_contents($file), true);
            if($data !== null){
                $this->totalConnections = $data['total_connections'] ?? 0;
                $this->packetsTranslated = $data['packets_translated'] ?? 0;
                $this->cacheHits = $data['cache_hits'] ?? 0;
            }
        }
    }
    public function incrementTotalConnections(): void{
        $this->totalConnections++;
    }
    public function incrementPacketsTranslated(): void{
        $this->packetsTranslated++;
    }
    public function incrementCacheHits(): void{
        $this->cacheHits++;
    }
    public function incrementTranslationErrors(): void{
        $this->translationErrors++;
    }
    public function getStatistics(): array{
        $registry = $this->plugin->getVersionRegistry();
        $sessions = $registry->getActiveSessions();

        $protocolDistribution = [];
        foreach($sessions as $session){
            $protocol = $session['protocol'];
            if(!isset($protocolDistribution[$protocol])){
                $protocolDistribution[$protocol] = 0;
            }
            $protocolDistribution[$protocol]++;
        }
        return [
            'total_connections' => $this->totalConnections,
            'active_sessions' => count($sessions),
            'packets_translated' => $this->packetsTranslated,
            'cache_hits' => $this->cacheHits,
            'translation_errors' => $this->translationErrors,
            'protocol_distribution' => $protocolDistribution
        ];
    }
    public function getServerProtocol(): int{
        return $this->serverProtocol;
    }
    public function saveStatistics(): void{
        $stats = $this->getStatistics();
        $file = $this->plugin->getDataFolder() . "statistics.json";
        file_put_contents($file, json_encode($stats, JSON_PRETTY_PRINT));
    }
}
