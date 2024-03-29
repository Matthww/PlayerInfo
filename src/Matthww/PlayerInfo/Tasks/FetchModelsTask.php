<?php
namespace Matthww\PlayerInfo\Tasks;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;

class FetchModelsTask extends AsyncTask {

    protected $path;
    protected $version;

    public function __construct(string $path, string $version) {
        $this->path = $path;
        $this->version = $version;
    }

    public function onRun(): void {
        $result = Internet::getURL("https://raw.githubusercontent.com/Matthww/PlayerInfo/master/resources/models.yml");
        if(is_null($result)) {
            $this->setResult(false);
            return;
        }

        file_put_contents($this->path. "models.yml", $result->getBody());
        $this->setResult(true);
    }
}
