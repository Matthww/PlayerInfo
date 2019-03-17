<?php
namespace Matthww\PlayerInfo\Tasks;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;

class FetchModelsTask extends AsyncTask {

    private $path;

    public function __construct(string $path) {
        $this->path = $path;
    }

    public function onRun() {
        $result = Internet::getURL("https://playerinfo.hillcraft.net/models.yml");
        if(!is_string($result)) {
            $this->setResult(false);
            return;
        }
        file_put_contents($this->path . "models.yml", $result);
        $this->setResult(true);
    }

    public function onCompletion(Server $server) {
        if($this->getResult() === true) {
            $server->getLogger()->notice("[PlayerInfo] Updated models to the latest version!");
        } else { // upon failure
            $server->getLogger()->notice("[PlayerInfo] Failed to update models to the latest version!");
        }
    }
}
