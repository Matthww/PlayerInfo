<?php

namespace Matthww\PlayerInfo;

use Matthww\PlayerInfo\Utils\SpoonDetector;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class PlayerInfo extends PluginBase implements Listener
{

    protected $DeviceOS;
    protected $DeviceModel;
    protected $UIProfile;
    protected $PlayerData;
    protected $target;

    public function onEnable()
    {
        file_put_contents($this->getDataFolder() . ".started", "true");
        SpoonDetector::printSpoon($this, 'spoon.txt');
    }

    public function onDisable()
    {
        $this->getLogger()->info("is disabled!");
    }

    public function onPacketReceived(DataPacketReceiveEvent $receiveEvent)
    {
        if ($receiveEvent->getPacket() instanceof LoginPacket) {
            $pk = $receiveEvent->getPacket();
            $this->PlayerData[$pk->username] = $pk->clientData;
        }
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args)
    {
        if (strtolower($command->getName()) == "playerinfo" or strtolower($command->getName()) == "pinfo") {

            $os = ["Unknown", "Android", "iOS", "OSX", "FireOS", "GearVR", "HoloLens", "Windows 10", "Windows", "Dedicated"];
            $UI = ["Classic UI", "Pocket UI"];

            if ($sender instanceof Player) {
                if ($sender->hasPermission("playerinfo.command.use")) {
                    if (!isset($args[0])) {
                        $cdata = $this->PlayerData[$sender->getName()];
                        $sender->sendMessage("§a§l===§r§aPlayer Info§a§l===");
                        $sender->sendMessage("§bName: §c" . $sender->getDisplayName());
                        $sender->sendMessage("§bIP: §c" . $sender->getAddress());
                        $sender->sendMessage("§bOS: §c" . $os[$cdata["DeviceOS"]]);
                        $sender->sendMessage("§bModel: §c" . $cdata["DeviceModel"]);
                        $sender->sendMessage("§bUI: §c" . $UI[$cdata["UIProfile"]]);
                        $sender->sendMessage("§a§l==============");
                        return true;
                    } else {
                        if ($this->getServer()->getPlayer($args[0])) {
                            $this->target = $this->getServer()->getPlayer($args[0]);
                            $cdata = $this->PlayerData[$this->target->getName()];
                            $sender->sendMessage("§a§l===§r§aPlayer Info§a§l===");
                            $sender->sendMessage("§bName: §c" . $this->target->getDisplayName());
                            $sender->sendMessage("§bIP: §c" . $this->target->getAddress());
                            $sender->sendMessage("§bOS: §c" . $os[$cdata["DeviceOS"]]);
                            $sender->sendMessage("§bModel: §c" . $cdata["DeviceModel"]);
                            $sender->sendMessage("§bUI: §c" . $UI[$cdata["UIProfile"]]);
                            $sender->sendMessage("§a§l==============");
                            return true;
                        } else {
                            $sender->sendMessage("§c[Error] Player not found");
                            return false;
                        }
                    }
                }
            } else {
                $sender->sendMessage("§cThis command can't be executed by the console.");
            }
        }
        return true;
    }
}
