<?php
declare(strict_types=1);
namespace MultiVersion\Handler;
use MultiVersion\Commands\MultiVersionCommand;
use MultiVersion\MultiVersion;
use MultiVersion\Network\PacketRegistry;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
final class CommandHandler extends PacketHandler{
    protected function initialize(): void{}
    public function register(PacketRegistry $registry): void{}
    public function registerMultiVersionCommands(): void{
        $this->registerMVCommand();
        $this->registerProtocolCommand();
        $this->registerVersionCommand();
    }
    private function registerMVCommand(): void{
        $command = new MultiVersionCommand($this->plugin);
        MultiVersion::getInstance()?->getServer()->getCommandMap()->register("multiversion", $command);
    }
    private function registerProtocolCommand(): void{
        $command = new class extends Command{
            private MultiVersion $plugin;
            public function __construct(){
                parent::__construct(
                    "protocol",
                    "Show your protocol",
                    "/protocol",
                    ["proto"]
                );
                $this->plugin = MultiVersion::getInstance();
                $this->setPermission("multiversion.command");
            }
            public function execute(CommandSender $sender, string $label, array $args): bool{
                if(!$this->testPermission($sender)){
                    return true;
                }
                if(!$sender instanceof Player){
                    $sender->sendMessage("§cThis command can only be used in-game");
                    return true;
                }
                $session = $this->plugin->getNetworkManager()->getSession($sender);
                if($session === null){
                    $sender->sendMessage("§cSession not found");
                    return true;
                }
                $sender->sendMessage("§aProtocol: §e" . $session->getProtocol() . " §7(" . $session->getClientVersion() . ")");
                return true;
            }
        };
        MultiVersion::getInstance()?->getServer()->getCommandMap()->register("protocol", $command);
    }
    private function registerVersionCommand(): void{
        $command = new class extends Command{
            public function __construct(){
                parent::__construct(
                    "mvversion",
                    "MultiVersion version",
                    "/mvversion",
                    ["mvv"]
                );
                $this->setPermission("multiversion.command");
            }
            public function execute(CommandSender $sender, string $label, array $args): bool{
                if(!$this->testPermission($sender)){
                    return true;
                }
                $sender->sendMessage("§aMultiVersion §7v1.0.0-PRODUCTION");
                $sender->sendMessage("§7Protocol-level compatibility layer for PocketMine-MP 5");
                return true;
            }
        };
        MultiVersion::getInstance()?->getServer()->getCommandMap()->register("mvversion", $command);
    }
}
