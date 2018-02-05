<?php

namespace Matthww\PlayerInfo;

use Matthww\PlayerInfo\utils\SpoonDetector;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\Listener;
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
        $this->saveDefaultConfig();
        SpoonDetector::printSpoon($this, 'spoon.txt');
        $this->getLogger()->notice("is enabled");
    }

    public function onDisable() {
        $this->getLogger()->notice("is disabled!");
    }

    public function onPacketReceived(DataPacketReceiveEvent $receiveEvent) {
        $pk = $receiveEvent->getPacket();
        if($pk instanceof LoginPacket) {
            $this->PlayerData[$pk->username] = $pk->clientData;
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if(strtolower($command->getName()) == "playerinfo" or strtolower($command->getName()) == "pinfo") {

            $os = ["Unknown", "Android", "iOS", "macOS", "FireOS", "GearVR", "HoloLens", "Windows 10", "Windows", "Dedicated", "Orbis", "NX"];
            $UI = ["Classic UI", "Pocket UI"];
            $Controls = ["Unknown", "Mouse", "Touch", "Controller"];
            $GUI = [-2 => "Minimum", -1 => "Medium", 0 => "Maximum"];

            if($sender->hasPermission("playerinfo.use")) {
                if($sender instanceof ConsoleCommandSender) {
                    if(isset($args[0])) {
                        $target = $this->getServer()->getPlayer($args[0]);
                    } else {
                        $sender->sendMessage(TF::RED . "[Error] Please specify a player");
                        return false;
                    }
                } else {
                    if($sender instanceof Player and !isset($args[0])) {
                        $target = $sender->getPlayer();
                    } else {
                        $target = $this->getServer()->getPlayer($args[0]);
                    }
                }
            } else {
                $sender->sendMessage(TF::RED . "[Error] No permission");
                return false;
            }
            if($target instanceof Player) {
                $cdata = $this->PlayerData[$target->getName()];
                $sender->sendMessage(TF::GREEN . TF::BOLD . "===" . TF::GREEN . "Player Info" . TF::GREEN . TF::BOLD . "===");
                if($this->getConfig()->get("Name") == true) {
                    $sender->sendMessage(TF::AQUA . "Name: " . TF::RED . $target->getDisplayName());
                }
                if($this->getConfig()->get("IP") == true) {
                    $sender->sendMessage(TF::AQUA . "IP: " . TF::RED . $target->getAddress());
                }
                if($this->getConfig()->get("Ping") == true) {
                    $sender->sendMessage(TF::AQUA . "Ping: " . TF::RED . $target->getPing() . "ms");
                }
                if($this->getConfig()->get("OS") == true) {
                    $sender->sendMessage(TF::AQUA . "OS: " . TF::RED . $os[$cdata["DeviceOS"]]);
                }
                if($this->getConfig()->get("Model") == true) {
                    $sender->sendMessage(TF::AQUA . "Model: " . TF::RED . $cdata["DeviceModel"]);
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
                return true;
            } else {
                $sender->sendMessage(TF::RED . "[Error] Player is not online");
                return false;
            }
        }
        return true;
    }
}
