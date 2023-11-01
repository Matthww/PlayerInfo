<?php
namespace Matthww\PlayerInfo\Tasks;

use pocketmine\command\CommandSender;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

class LoadTask extends Task {

    public $plugin;

    protected $sender;
    protected $target;

    public function __construct($plugin, CommandSender $sender, string $target) {
        $this->plugin = $plugin;
        $this->sender = $sender;
        $this->target = $target;
    }

    public function getPlugin() {
        return $this->plugin;
    }

    public function onRun(): void {
        if(!file_exists($this->getPlugin()->getDataFolder() . "players/" . strtolower($this->target) . ".json")) {
            $this->sender->sendMessage(TF::colorize("&c[PlayerInfo] Player &f". $this->target . " &cis not online or does not exist!"));
        } else {
            $data = new Config($this->getPlugin()->getDataFolder() . "players/" . strtolower($this->target) . ".json", Config::JSON);
            $this->sender->sendMessage(TF::colorize("&a&l=== &r&aPlayerInfo &a&l==="));
            $this->sender->sendMessage(TF::colorize("&bName: &c" . $data->get("Name")));
            $this->sender->sendMessage(TF::colorize("&bModel: &c" . $data->get("Model")));
            $this->sender->sendMessage(TF::colorize("&bOS: &c" . $data->get("OS")));
            $this->sender->sendMessage(TF::colorize("&bIP: &c" . $data->get("IP")));
            $this->sender->sendMessage(TF::colorize("&bPort: &c" . $data->get("Port")));
            $this->sender->sendMessage(TF::colorize("&bUI: &c" . $data->get("UI")));
            $this->sender->sendMessage(TF::colorize("&bGUI Scale: &c" . $data->get("GUI")));
            $this->sender->sendMessage(TF::colorize("&bControls: &c" . $data->get("Controls")));
            $this->sender->sendMessage(TF::colorize("&UUID: &c" . $data->get("UUID")));
            $this->sender->sendMessage(TF::colorize("&Health: &c" . $data->get("Health")));
            $this->sender->sendMessage(TF::colorize("&Position: &c" . $data->get("Position")));
            $this->sender->sendMessage(TF::colorize("&a&l================"));
        }
    }
}
