<?php
namespace Matthww\PlayerInfo\Tasks;

use pocketmine\scheduler\Task;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

class SaveTask extends Task {

    public $plugin;
    
    protected $date;
    protected $player;
    protected $model;
    protected $os;
    protected $ip;
    protected $port;
    protected $UI;
    protected $GUI;
    protected $controls;
    protected $uuid;
    protected $health;
    protected $position;

    public function __construct($plugin, string $date, string $player, string $model, string $os, string $ip, string $port, string $UI, string $GUI, string $controls, string $uuid, string $health, string $position) {
        $this->plugin = $plugin;
        $this->date = $date;
        $this->player = $player;
        $this->model = $model;
        $this->os = $os;
        $this->ip = $ip;
        $this->port = $port;
        $this->UI = $UI;
        $this->GUI = $GUI;
        $this->controls = $controls;
        $this->uuid = $uuid;
        $this->health = $health;
        $this->position = $position;
    }

    public function getPlugin() {
        return $this->plugin;
    }

    public function onRun(): void {
        $data = new Config($this->getPlugin()->getDataFolder() . "players/" .$this->player."/". strtolower($this->player)."-".$this->date. ".json", Config::JSON);
        $data->set("Date", $this->date);
        $data->set("Name", $this->player);
        $data->set("Model", $this->model);
        $data->set("OS", $this->os);
        $data->set("IP", $this->ip);
        $data->set("Port", $this->port);
        $data->set("UI", $this->UI);
        $data->set("GUI", $this->GUI);
        $data->set("Controls", $this->controls);
        $data->set("UUID", $this->uuid);
        $data->set("Health", $this->health);
        $data->set("Position", $this->position);
        $data->save();
        $data->reload();
    }
}
