<?php

namespace Matthww\PlayerInfo;

use Matthww\PlayerInfo\utils\SpoonDetector;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class PlayerInfo extends PluginBase implements Listener {

    protected $DeviceOS;
    protected $DeviceModel;
    protected $UIProfile;
    protected $PlayerData;
    protected $config;

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        $this->getLogger()->notice("is enabled");
        SpoonDetector::printSpoon($this, 'spoon.txt');
    }

    public function onDisable() {
        $this->getLogger()->notice("is disabled!");
    }

    public function onPacketReceived(DataPacketReceiveEvent $receiveEvent) {
        if ($receiveEvent->getPacket() instanceof LoginPacket) {
            $pk = $receiveEvent->getPacket();
            $this->PlayerData[$pk->username] = $pk->clientData;
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{ 
        if (strtolower($command->getName()) == "playerinfo" or strtolower($command->getName()) == "pinfo") {

            $os = ["Unknown", "Android", "iOS", "macOS", "FireOS", "GearVR", "HoloLens", "Windows 10", "Windows", "Dedicated"];
            $UI = ["Classic UI", "Pocket UI"];
            $Controls = ["Unknown", "Mouse", "Touch", "Controller"];
            $GUI = [-2 => "Minimum", -1 => "Medium", 0 => "Maximum"];

            if ($sender->hasPermission("playerinfo.use")) {
                if (isset($args[0])) {
                    if ($this->getServer()->getPlayer($args[0])) {
                        $target = $this->getServer()->getPlayer($args[0]);
                        $cdata = $this->PlayerData[$target->getName()];
                        $sender->sendMessage("§a§l===§r§aPlayer Info§a§l===");
                        if ($this->getConfig()->get("Name") == true) {
                            $sender->sendMessage("§bName: §c" . $target->getDisplayName());
                        }
                        if ($this->getConfig()->get("IP") == true) {
                            $sender->sendMessage("§bIP: §c" . $target->getAddress());
                        }
                        if ($this->getConfig()->get("OS") == true) {
                            $sender->sendMessage("§bOS: §c" . $os[$cdata["DeviceOS"]]);
                        }
                        if ($this->getConfig()->get("Model") == true) {
                            $sender->sendMessage("§bModel: §c" . $cdata["DeviceModel"]);
                        }
                        if ($this->getConfig()->get("UI") == true) {
                            $sender->sendMessage("§bUI: §c" . $UI[$cdata["UIProfile"]]);
                        }
                        if ($this->getConfig()->get("GUI") == true) {
                            $sender->sendMessage("§bGUI Scale: §c" . $GUI[$cdata["GuiScale"]]);
                        }
                        if ($this->getConfig()->get("Controls") == true) {
                            $sender->sendMessage("§bControls: §c" . $Controls[$cdata["CurrentInputMode"]]);
                        }
                        $sender->sendMessage("§a§l==============");
                        return true;
                    } else {
                        $sender->sendMessage("§c[Error] Player not found");
                    }
                } else {
                    if ($sender instanceof Player) {
                        $cdata = $this->PlayerData[$sender->getName()];
                        $sender->sendMessage("§a§l===§r§aPlayer Info§a§l===");
                        if ($this->getConfig()->get("Name") == true) {
                            $sender->sendMessage("§bName: §c" . $sender->getDisplayName());
                        }
                        if ($this->getConfig()->get("IP") == true) {
                            $sender->sendMessage("§bIP: §c" . $sender->getAddress());
                        }
                        if ($this->getConfig()->get("OS") == true) {
                            $sender->sendMessage("§bOS: §c" . $os[$cdata["DeviceOS"]]);
                        }
                        if ($this->getConfig()->get("Model") == true) {
                            $sender->sendMessage("§bModel: §c" . $cdata["DeviceModel"]);
                        }
                        if ($this->getConfig()->get("UI") == true) {
                            $sender->sendMessage("§bUI: §c" . $UI[$cdata["UIProfile"]]);
                        }
                        if ($this->getConfig()->get("GUI") == true) {
                            $sender->sendMessage("§bGUI Scale: §c" . $GUI[$cdata["GuiScale"]]);
                        }
                        if ($this->getConfig()->get("Controls") == true) {
                            $sender->sendMessage("§bControls: §c" . $Controls[$cdata["CurrentInputMode"]]);
                        }
                        $sender->sendMessage("§a§l==============");
                        return true;
                    } else {
                        $sender->sendMessage("§c[Error] Please specify a player");
                    }
                }
            }
        }
        return true;
    }
}
