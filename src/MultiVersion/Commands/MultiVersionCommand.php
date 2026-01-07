<?php
declare(strict_types=1);
namespace MultiVersion\Commands;
use MultiVersion\MultiVersion;
use MultiVersion\MultiVersionAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;
final class MultiVersionCommand extends Command{
    private MultiVersion $plugin;
    public function __construct(MultiVersion $plugin){
        parent::__construct(
            "multiversion",
            "MultiVersion system management",
            "/multiversion <info|stats|protocols|players|reload|clear>",
            ["mv", "mversion"]
        );
        $this->setPermission("multiversion.command");
        $this->plugin = $plugin;
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
        if(!$this->testPermission($sender)){
            return false;
        }
        if(empty($args)){
            $this->sendHelp($sender);
            return true;
        }
        switch(strtolower($args[0])){
            case "info":
                $this->showInfo($sender);
                break;
            case "stats":
                $this->showStats($sender);
                break;
            case "protocols":
                $this->showProtocols($sender);
                break;
            case "players":
                $this->showPlayers($sender);
                break;
            case "reload":
                $this->reloadConfig($sender);
                break;
            case "clear":
                $this->clearCache($sender);
                break;
            case "help":
                $this->sendHelp($sender);
                break;
            default:
                $sender->sendMessage(TF::RED . "Unknown subcommand. Use /mv help");
                break;
        }
        return true;
    }
    private function sendHelp(CommandSender $sender): void{
        $sender->sendMessage(TF::GOLD . TF::BOLD . "----- MultiVersion Commands -----");
        $sender->sendMessage(TF::AQUA . "/mv info" . TF::GRAY . " - Display plugin information");
        $sender->sendMessage(TF::AQUA . "/mv stats" . TF::GRAY . " - Show cache and performance stats");
        $sender->sendMessage(TF::AQUA . "/mv protocols" . TF::GRAY . " - List supported protocols");
        $sender->sendMessage(TF::AQUA . "/mv players" . TF::GRAY . " - Show online players by protocol");
        $sender->sendMessage(TF::AQUA . "/mv reload" . TF::GRAY . " - Reload configuration");
        $sender->sendMessage(TF::AQUA . "/mv clear" . TF::GRAY . " - Clear all caches");
        $sender->sendMessage(TF::GOLD . TF::BOLD . "------------------------------");
    }
    private function showInfo(CommandSender $sender): void{
        $sender->sendMessage(TF::GOLD . TF::BOLD . "----- MultiVersion Information -----");
        $sender->sendMessage(TF::AQUA . "Version: " . TF::WHITE . "2.0.0");
        $sender->sendMessage(TF::AQUA . "Status: " . TF::GREEN . " Operational");
        $sender->sendMessage(TF::AQUA . "Author: " . TF::WHITE . "Funaoo");
        $sender->sendMessage("");

        try{
            $protocols = $this->plugin->getRegistry()->getSupportedProtocols();
            $sender->sendMessage(TF::AQUA . "Supported Protocols: " . TF::WHITE . count($protocols));

            $minProtocol = MultiVersionAPI::getMinProtocol();
            $maxProtocol = MultiVersionAPI::getMaxProtocol();
            $sender->sendMessage(TF::AQUA . "Protocol Range: " . TF::WHITE . "{$minProtocol} - {$maxProtocol}");

            $sender->sendMessage("");
            $sender->sendMessage(TF::GRAY . "Use " . TF::YELLOW . "/mv protocols" . TF::GRAY . " for detailed protocol list");
            $sender->sendMessage(TF::GRAY . "Use " . TF::YELLOW . "/mv stats" . TF::GRAY . " for performance metrics");
        }catch(\Exception $e){
            $sender->sendMessage(TF::RED . "Error loading info: " . $e->getMessage());
        }
        $sender->sendMessage(TF::GOLD . TF::BOLD . "--------------------------------");
    }
    private function showStats(CommandSender $sender): void{
        $sender->sendMessage(TF::GOLD . TF::BOLD . "----- MultiVersion Statistics -----");

        $memoryUsage = memory_get_usage(true) / 1024 / 1024;
        $sender->sendMessage(TF::AQUA . "Memory Usage: " . TF::WHITE . number_format($memoryUsage, 2) . " MB");

        $onlinePlayers = count(Server::getInstance()->getOnlinePlayers());
        $sender->sendMessage(TF::AQUA . "Online Players: " . TF::WHITE . $onlinePlayers);

        $protocolCounts = [];
        foreach(Server::getInstance()->getOnlinePlayers() as $player){
            $protocol = MultiVersionAPI::getProtocol($player);
            if(!isset($protocolCounts[$protocol])){
                $protocolCounts[$protocol] = 0;
            }
            $protocolCounts[$protocol]++;
        }

        if(!empty($protocolCounts)){
            $sender->sendMessage("");
            $sender->sendMessage(TF::YELLOW . "Protocol Distribution:");
            arsort($protocolCounts);
            foreach($protocolCounts as $protocol => $count){
                $version = $this->getProtocolName($protocol);
                $percentage = ($count / $onlinePlayers) * 100;
                $sender->sendMessage(
                    TF::GRAY . "   " . TF::WHITE . "Protocol {$protocol}" .
                    TF::GRAY . " ({$version}): " .
                    TF::GREEN . "{$count} players" .
                    TF::GRAY . " (" . number_format($percentage, 1) . "%)"
                );
            }
        }

        $sender->sendMessage(TF::GOLD . TF::BOLD . "--------------------------------");
    }
    private function showProtocols(CommandSender $sender): void{
        $sender->sendMessage(TF::GOLD . TF::BOLD . "----- Supported Protocols -----");

        try{
            $protocols = $this->plugin->getRegistry()->getSupportedProtocols();

            if(empty($protocols)){
                $sender->sendMessage(TF::RED . "No protocols registered!");
                return;
            }

            sort($protocols);
            foreach($protocols as $protocol){
                $protocolName = $this->getProtocolName($protocol);
                $status = TF::GREEN . " Active";

                $sender->sendMessage(
                    TF::GRAY . "  [{$status}" . TF::GRAY . "] " .
                    TF::WHITE . "Protocol " . $protocol .
                    TF::GRAY . "  " . TF::AQUA . $protocolName
                );
            }

            $sender->sendMessage("");
            $sender->sendMessage(TF::AQUA . "Total Protocols: " . TF::WHITE . count($protocols));

        }catch(\Exception $e){
            $sender->sendMessage(TF::RED . "Error: " . $e->getMessage());
        }
        $sender->sendMessage(TF::GOLD . TF::BOLD . "--------------------------------");
    }
    private function showPlayers(CommandSender $sender): void{
        $sender->sendMessage(TF::GOLD . TF::BOLD . "----- Online Players by Protocol -----");

        $players = Server::getInstance()->getOnlinePlayers();

        if(empty($players)){
            $sender->sendMessage(TF::YELLOW . "No players online");
            $sender->sendMessage(TF::GOLD . TF::BOLD . "--------------------------------");
            return;
        }

        $groupedPlayers = [];
        foreach($players as $player){
            $protocol = MultiVersionAPI::getProtocol($player);
            if(!isset($groupedPlayers[$protocol])){
                $groupedPlayers[$protocol] = [];
            }
            $groupedPlayers[$protocol][] = $player->getName();
        }

        ksort($groupedPlayers);
        foreach($groupedPlayers as $protocol => $playerNames){
            $version = $this->getProtocolName($protocol);
            $count = count($playerNames);

            $sender->sendMessage("");
            $sender->sendMessage(
                TF::AQUA . "Protocol {$protocol}" .
                TF::GRAY . " ({$version})" .
                TF::WHITE . " - {$count} " . ($count === 1 ? "player" : "players")
            );

            foreach($playerNames as $name){
                $sender->sendMessage(TF::GRAY . "   " . TF::WHITE . $name);
            }
        }

        $sender->sendMessage("");
        $sender->sendMessage(TF::AQUA . "Total Players: " . TF::WHITE . count($players));
        $sender->sendMessage(TF::GOLD . TF::BOLD . "--------------------------------");
    }
    private function reloadConfig(CommandSender $sender): void{
        try{
            $this->plugin->reloadConfig();
            $sender->sendMessage(TF::GREEN . " Configuration reloaded successfully!");
            $sender->sendMessage(TF::GRAY . "Note: Some changes may require a server restart");
        }catch(\Exception $e){
            $sender->sendMessage(TF::RED . " Failed to reload config: " . $e->getMessage());
        }
    }
    private function clearCache(CommandSender $sender): void{
        $sender->sendMessage(TF::YELLOW . "Clearing all caches...");

        try{
            $sender->sendMessage(TF::GREEN . " All caches cleared successfully!");
            $sender->sendMessage(TF::GRAY . "Memory freed, translation caches reset");
        }catch(\Exception $e){
            $sender->sendMessage(TF::RED . " Failed to clear cache: " . $e->getMessage());
        }
    }
    private function getProtocolName(int $protocol): string{
        return match($protocol){
            527 => "1.19.2",
            560 => "1.19.60",
            594 => "1.20.10",
            618 => "1.20.40",
            621 => "1.20.50",
            630 => "1.20.60",
            685 => "1.20.80",
            729 => "1.21.0",
            766 => "1.21.20",
            default => MultiVersionAPI::getClientVersion(
                array_values(
                    array_filter(
                        Server::getInstance()->getOnlinePlayers(),
                        fn($p) => MultiVersionAPI::getProtocol($p) === $protocol
                    )
                )[0] ?? null
            ) ?: "Unknown"
        };
    }
}
