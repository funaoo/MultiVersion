<?php
declare(strict_types=1);
namespace MultiVersion;
use MultiVersion\Network\MVPacketInterceptor;
use MultiVersion\Network\Proto\ProtocolRegistry;
use MultiVersion\Commands\MultiVersionCommand;
use pocketmine\plugin\PluginBase;
final class MultiVersion extends PluginBase{
    private static ?self $instance = null;
    private ProtocolRegistry $registry;
    private MVPacketInterceptor $interceptor;
    public static function getInstance(): self{
        if(self::$instance === null){
            throw new \RuntimeException("Plugin not initialized");
        }
        return self::$instance;
    }
    public function onLoad(): void{
        self::$instance = $this;
    }
    public function onEnable(): void{
        $this->saveDefaultConfig();

        $this->registry = new ProtocolRegistry();
        $this->interceptor = new MVPacketInterceptor($this->registry);

        $this->getServer()->getPluginManager()->registerEvents($this->interceptor, $this);

        $command = new MultiVersionCommand($this);
        $this->getServer()->getCommandMap()->register("multiversion", $command);

        $protocols = $this->registry->getSupportedProtocols();
        $this->getLogger()->info("MultiVersion enabled - Protocols: " . implode(", ", $protocols));
    }
    public function onDisable(): void{
        $this->getLogger()->info("MultiVersion disabled");
    }
    public function getRegistry(): ProtocolRegistry{
        return $this->registry;
    }
}
