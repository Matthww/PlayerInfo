<?php
namespace Matthww\PlayerInfo\Tasks;

use pocketmine\scheduler\Task;
use pocketmine\utils\Config;

class SaveTask extends Task {

    public $plugin;

    protected $player;
    protected $model;
    protected $os;
    protected $ip;
    protected $UI;
    protected $GUI;
    protected $controls;

    public function __construct($plugin, string $player, string $model, string $os, string $ip, string $UI, string $GUI, string $controls) {
        $this->plugin = $plugin;
        $this->player = $player;
        $this->model = $model;
        $this->os = $os;
        $this->ip = $ip;
        $this->UI = $UI;
        $this->GUI = $GUI;
        $this->controls = $controls;
    }

    public function getPlugin() {
        return $this->plugin;
    }

    public function onRun(int $tick) {
        $data = new Config($this->getPlugin()->getDataFolder() . "players/" . strtolower($this->player) . ".json", Config::JSON);
        $data->set("Name", $this->player);
        $data->set("Model", $this->model);
        $data->set("OS", $this->os);
        $data->set("IP", $this->ip);
        $data->set("UI", $this->UI);
        $data->set("GUI", $this->GUI);
        $data->set("Controls", $this->controls);
        $data->save();
        $data->reload();
    }
}
