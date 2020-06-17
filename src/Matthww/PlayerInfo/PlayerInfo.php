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
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;

class PlayerInfo extends PluginBase implements Listener {

    protected $DeviceOS;
    protected $DeviceModel;
    protected $UIProfile;
    protected $PlayerData;
    protected $config;

    public function onEnable() {
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
    }

    public function onPacketReceived(DataPacketReceiveEvent $receiveEvent) {
        $pk = $receiveEvent->getPacket();
        if($pk instanceof LoginPacket) {
            $this->PlayerData[$pk->username] = $pk->clientData;
        }
    }

    public function onJoin(PlayerJoinEvent $joinEvent) {
        if($this->getConfig()->get("Save") == true) {
            $player = $joinEvent->getPlayer();
            if (!in_array($player->getName(), $this->PlayerData)) {
                return false;
            }
            $cdata = $this->PlayerData[$player->getName()];
            $os = ["Unknown", "Android", "iOS", "macOS", "FireOS", "GearVR", "HoloLens", "Windows 10", "Windows", "Dedicated", "Orbis", "Playstation 4", "Nintento Switch", "Xbox One"];
            $UI = ["Classic UI", "Pocket UI"];
            $Controls = ["Unknown", "Mouse", "Touch", "Controller"];
            $GUI = [-2 => "Minimum", -1 => "Medium", 0 => "Maximum"];

            $this->getScheduler()->scheduleTask(new SaveTask(
                $this,
                $player->getName(),
                $this->getModel($cdata["DeviceModel"]),
                $os[$cdata["DeviceOS"]],
                $player->getAddress(),
                $player->getXuid(),
                $player->getUniqueId(),
                $UI[$cdata["UIProfile"]],
                $GUI[$cdata["GuiScale"]],
                $Controls[$cdata["CurrentInputMode"]]
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

            $os = ["Unknown", "Android", "iOS", "macOS", "FireOS", "GearVR", "HoloLens", "Windows 10", "Windows", "Dedicated", "Orbis", "NX"];
            $UI = ["Classic UI", "Pocket UI"];
            $Controls = ["Unknown", "Mouse", "Touch", "Controller"];
            $GUI = [-2 => "Minimum", -1 => "Medium", 0 => "Maximum"];

            if(!$sender->hasPermission("playerinfo.use")) {
                $sender->sendMessage(TF::RED . "[PlayerInfo] No permission");
                return false;
            }
            if(!isset($args[0])) {
                $sender->sendMessage(TF::RED . "[PlayerInfo] Please specify a player");
                return false;
            }
            $target = $this->getServer()->getPlayer($args[0]);

            if(!$target instanceof Player) {
                if($this->getConfig()->get("Save") == true) {
                    $this->getScheduler()->scheduleTask(new LoadTask($this, $sender, $args[0]));
                    return true;
                } else {
                    $sender->sendMessage(TF::RED . "[PlayerInfo] Player is not online");
                    return false;
                }
            }
            if($target instanceof Player) {
                $cdata = $this->PlayerData[$target->getName()];
                $sender->sendMessage(TF::GREEN . TF::BOLD . "===" . TF::GREEN . "PlayerInfo" . TF::GREEN . TF::BOLD . "===");
                if($this->getConfig()->get("Name") == true) {
                    $sender->sendMessage(TF::AQUA . "Name: " . TF::RED . $target->getDisplayName());
                }
                if($this->getConfig()->get("IP") == true) {
                    $sender->sendMessage(TF::AQUA . "IP: " . TF::RED . $target->getAddress());
                }
                if($this->getConfig()->get("Ping") == true) {
                    $sender->sendMessage(TF::AQUA . "Ping: " . TF::RED . $target->getPing() . "ms");
                }
                if($this->getConfig()->get("XUID") == true){
                    $sender->sendMessage(TF::AQUA . "XUID: " . TF::RED . $target->getXuid());
                }
                if($this->getConfig()->get("UUID") == true){
                    $sender->sendMessage(TF::AQUA . "UUID: " . TF::RED . $target->getUniqueId());
                }
                if($this->getConfig()->get("OS") == true) {
                    $sender->sendMessage(TF::AQUA . "OS: " . TF::RED . $os[$cdata["DeviceOS"]]);
                }
                if($this->getConfig()->get("Model") == true) {
                    $sender->sendMessage(TF::AQUA . "Model: " . TF::RED . $this->getModel($cdata["DeviceModel"]));
                }
                if($this->getConfig()->get("UI") == true) {
                    $sender->sendMessage(TF::AQUA . "UI: " . TF::RED . $UI[$cdata["UIProfile"]]);
                }
                if($this->getConfig()->get("GUI") == true) {
                    $sender->sendMessage(TF::AQUA . "GUI Scale: " . TF::RED . $GUI[$cdata["GuiScale"]]);
                }
                if($this->getConfig()->get("Controls") == true) {
                    $sender->sendMessage(TF::AQUA . "Controls: " . TF::RED . $Controls[$cdata["CurrentInputMode"]]);
                }
                if($this->getConfig()->get("Health") == true) {
                    $sender->sendMessage(TF::AQUA . "Health: " . TF::RED . $target->getHealth() . "HP");
                }
                if($this->getConfig()->get("Position") == true) {
                    $sender->sendMessage(TF::AQUA . "Position: " . TF::RED . "X: " . $target->getFloorX() . ", Y: " . $target->getFloorY() . ", Z: " . $target->getFloorZ());
                }
                $sender->sendMessage(TF::GREEN . TF::BOLD . "================");
            }
        }
        return true;
    }
}
