<?php
namespace Matthww\PlayerInfo\Tasks;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;

class FetchModelsTask extends AsyncTask {

    private $path;
    private $version;

    public function __construct(string $path, string $version) {
        $this->path = $path;
        $this->version = $version;
    }

    public function onRun() {
        print($this->version);
        $result = Internet::getURL("https://playerinfo.hillcraft.net/models.yml?v=" . $this->version);
        if(!is_string($result)) {
            $this->setResult(false);
            return;
        }
        file_put_contents($this->path . "models.yml", $result);
        $this->setResult(true);
    }
}
