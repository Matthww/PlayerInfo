<?php

namespace Matthww\PlayerInfo;

use Matthww\PlayerInfo\Tasks\FetchModelsTask;
use Matthww\PlayerInfo\Tasks\LoadTask;
use Matthww\PlayerInfo\Tasks\SaveTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;

class PlayerInfo extends PluginBase implements Listener {
    protected $DeviceOS;
    protected $DeviceModel;
    protected $UIProfile;
    protected $PlayerData;
    protected $config;

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
       
        if(!is_dir($this->getDataFolder())) {
            mkdir($this->getDataFolder());
        }
        if(!is_dir($this->getDataFolder() . "players")) {
            mkdir($this->getDataFolder() . "players");
        }
        if(!file_exists($this->getDataFolder() . "config.yml")) {
            $this->saveDefaultConfig();
        }
        $this->getServer()->getAsyncPool()->submitTask(new FetchModelsTask($this->getDataFolder(), $this->getDescription()->getVersion()));
        // const $lokalizacja = $this->getDataFolder();
        
    }

    public function onJoin(PlayerJoinEvent $joinEvent) {
        if($this->getConfig()->get("Save") == true) {
            $player = $joinEvent->getPlayer();
            if(!is_dir($this->getDataFolder() . "players/".$player->getName())) {
                $this->getLogger()->info(TF::YELLOW."[PLAYERINFODB] > User not found in db... adding one.");
                mkdir($this->getDataFolder() . "players/".$player->getName());
            }
            $this->getLogger()->info(TF::GREEN.'[PLAYERINFO] > Adding user session '.$player->getName().' to history');
            date_default_timezone_set("UTC");
            $date = date('m-d-Y_h-i-s_a', time());  // save date and time (Hours need to being separated with "-" too for windows system because it is crazy....)
        
            $cdata = $player->getNetworkSession()->getPlayerInfo()->getExtraData();
            $os = ["Unknown", "Android", "iOS", "macOS", "FireOS", "GearVR", "HoloLens", "Windows 10", "Windows", "Dedicated", "Orbis", "Playstation 4", "Nintento Switch", "Xbox One"];
            $UI = ["Classic UI", "Pocket UI"];
            $Controls = ["Unknown", "Mouse", "Touch", "Controller"];
            $GUI = [-2 => "Minimum", -1 => "Medium", 0 => "Maximum"];
            
            $this->getScheduler()->scheduleTask(new SaveTask(
                $this,
                $date,
                $player->getName(),
                $this->getModel($cdata["DeviceModel"] ?? -1),
                $os[$cdata["DeviceOS"] ?? -1],
                $player->getNetworkSession()->getIp(),
                $player->getNetworkSession()->getPort(),
                $UI[$cdata["UIProfile"] ?? -1],
                $GUI[$cdata["GuiScale"] ?? -1],
                $Controls[$cdata["CurrentInputMode"] ?? -1],
                $player->getUniqueId(),
                $player->getHealth(). " HP",
                "X: " . $player->getPosition()->getFloorX() . ", Y: " . $player->getPosition()->getFloorY() . ", Z: " . $player->getPosition()->getFloorZ()
            ));
            
        }
    }

    public function getModel(string $model) {
        $models = yaml_parse_file($this->getDataFolder() . "models.yml");

        if(isset($models[$model])) {
            return $models[$model];
        }
        return $model;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if(strtolower($command->getName()) == "playerinfo" or strtolower($command->getName()) == "pinfo") {
            $os = ["Unknown", "Android", "iOS", "macOS", "FireOS", "GearVR", "HoloLens", "Windows 10", "Windows", "Dedicated", "Orbis", "Playstation 4", "Nintento Switch", "Xbox One"];
            $UI = ["Classic UI", "Pocket UI"];
            $Controls = ["Unknown", "Mouse", "Touch", "Controller"];
            $GUI = [-2 => "Minimum", -1 => "Medium", 0 => "Maximum"];

            if(!$sender->hasPermission("playerinfo.use")) {
                $sender->sendMessage(TF::RED . "[PlayerInfo] You don't have permission to use this command!");
                return false;
            }
            if(!isset($args[0])) {
                $sender->sendMessage(TF::RED . "[PlayerInfo] Please specify a player");
                return false;
            }
            $target = $this->getServer()->getPlayerExact($args[0]);

            if(!$target instanceof Player) {
                if($this->getConfig()->get("Save") == true) {
                    $this->getScheduler()->scheduleTask(new LoadTask($this, $sender, $args[0]));
                    return true;
                } else {
                    $sender->sendMessage(TF::RED . "[PlayerInfo] Player " .$args[0]. " is not online or does not exist!");
                    return false;
                }
            }
            if($target instanceof Player) {
                $cdata = $target->getNetworkSession()->getPlayerInfo()->getExtraData();
                $sender->sendMessage(TF::GREEN . TF::BOLD . "=== " . TF::GREEN . "PlayerInfo" . TF::GREEN . TF::BOLD . " ===");
                if($this->getConfig()->get("Name") == true) {
                    $sender->sendMessage(TF::AQUA . "Name: " . TF::RED . $target->getDisplayName());
                }
                if($this->getConfig()->get("Model") == true) {
                    $sender->sendMessage(TF::AQUA . "Model: " . TF::RED . $this->getModel($cdata["DeviceModel"] ?? -1));
                }
                if($this->getConfig()->get("OS") == true) {
                    $sender->sendMessage(TF::AQUA . "OS: " . TF::RED . $os[$cdata["DeviceOS"] ?? -1]);
                }
                if($this->getConfig()->get("IP") == true) {
                    $sender->sendMessage(TF::AQUA . "IP: " . TF::RED . $target->getNetworkSession()->getIp());
                }
                if($this->getConfig()->get("Port") == true) {
                    $sender->sendMessage(TF::AQUA . "Port: " . TF::RED . $target->getNetworkSession()->getPort());
                }
                if($this->getConfig()->get("Ping") == true) {
                    $sender->sendMessage(TF::AQUA . "Ping: " . TF::RED . $target->getNetworkSession()->getPing() . "ms");
                }
                if($this->getConfig()->get("UI") == true) {
                    $sender->sendMessage(TF::AQUA . "UI: " . TF::RED . $UI[$cdata["UIProfile"] ?? -1]);
                }
                if($this->getConfig()->get("GUI") == true) {
                    $sender->sendMessage(TF::AQUA . "GUI Scale: " . TF::RED . $GUI[$cdata["GuiScale"] ?? -1]);
                }
                if($this->getConfig()->get("Controls") == true) {
                    $sender->sendMessage(TF::AQUA . "Controls: " . TF::RED . $Controls[$cdata["CurrentInputMode"] ?? -1]);
                }
                if($this->getConfig()->get("UUID") == true) {
                    $sender->sendMessage(TF::AQUA . "UUID: " . TF::RED . $target->getUniqueId());
                }
                if($this->getConfig()->get("Health") == true) {
                    $sender->sendMessage(TF::AQUA . "Health: " . TF::RED . $target->getHealth() . " HP");
                }
                if($this->getConfig()->get("Position") == true) {
                    $sender->sendMessage(TF::AQUA . "Position: " . TF::RED . "X: " . $target->getPosition()->getFloorX() . ", Y: " . $target->getPosition()->getFloorY() . ", Z: " . $target->getPosition()->getFloorZ());
                }
                $sender->sendMessage(TF::GREEN . TF::BOLD . "==================");
            }
        }
        return true;
    }
}
