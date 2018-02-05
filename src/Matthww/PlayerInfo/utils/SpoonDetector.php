<?php
namespace Matthww\PlayerInfo\utils;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;

/**
 * This class is deliberately meant to be silly
 * Class SpoonDetector
 * @package Matthww\PlayerInfo\utils\SpoonDetector
 */
final class SpoonDetector{

    private static $subtleAsciiSpoon = "   
         ___ _ __   ___   ___  _ __  
        / __| '_ \\ / _ \\ / _ \\| '_ \\ 
        \\__ \\ |_) | (_) | (_) | | | |
        |___/ .__/ \\___/ \\___/|_| |_|
            | |                      
            |_|                      
    ";

    private static $spoonTxtContent = "
    The author of this plugin does not provide support for third-party builds of 
    PocketMine-MP (spoons). Spoons detract from the overall quality of the MCPE plugin environment, which is already 
    lacking in quality. They force plugin developers to waste time trying to support conflicting APIs.
    
    In order to begin using this plugin you must understand that you will be offered no support. 
    
    Furthermore, the GitHub issue tracker for this project is targeted at vanilla PocketMine only. Any bugs you create which don't affect vanilla PocketMine will be deleted.
    
    Have you read and understood the above (type 'yes' after the question mark)?";

    const THINGS_THAT_ARE_NOT_SPOONS = [
        'PocketMine-MP'
    ];


    public final static function simpleCheck(Server $server) : bool {
        return !in_array(Server::getInstance()->getName(), SpoonDetector::THINGS_THAT_ARE_NOT_SPOONS);
    }

    public static final function contentCheck(Server $server): bool{
        $reflectionClass = new \ReflectionClass($server);
        $method = $reflectionClass->getMethod("getName");
        $start = $method->getStartLine();
        $end = $method->getEndLine();

        $filename = $method->getFileName();
        $length = $end - $start;

        $source = file($filename);
        $body = implode("", array_slice($source, $start, $length));

        if(strpos($body, "(") !== false || strpos($body, ")") !== false){
            $server->getLogger()->info("Your server may be attempting to block SpoonDetector from running. SpoonDetector will continue to run regardless. If you are the developer of this spoon would like to be exempted from spoon detection, create a new API versioning system so existing PM plugins don't run, and then create an issue at Falkirks/spoondetector.");
            return true;
        }
        foreach ($source as $line){
            if(strpos($line, "SpoonDetector") !== false){
                $server->getLogger()->info("Your server may be attempting to block SpoonDetector from running. SpoonDetector will continue to run regardless. If you are the developer of this spoon would like to be exempted from spoon detection, create a new API versioning system so existing PM plugins don't run, and then create an issue at Falkirks/spoondetector.");
                return true;
            }
        }
        return false;
    }

    public final static function isThisSpoon() : bool {
        $server = Server::getInstance();
        return self::simpleCheck($server) || self::contentCheck($server);

    }

    private final static function contentValid(string $content): bool {
        return (strpos($content, self::$spoonTxtContent) !== false) && (strrpos($content, "yes") > strrpos($content, "?"));
    }

    public final static function printSpoon(PluginBase $pluginBase, $fileToCheck = "spoon.txt"){
        if(self::isThisSpoon()){
            if(!file_exists($pluginBase->getDataFolder() . $fileToCheck)){
                file_put_contents($pluginBase->getDataFolder() . $fileToCheck, self::$spoonTxtContent);
            }
            if(!self::contentValid(file_get_contents($pluginBase->getDataFolder() . $fileToCheck))) {
                $pluginBase->getLogger()->info(self::$subtleAsciiSpoon);
                $pluginBase->getLogger()->warning("You are attempting to run " . $pluginBase->getDescription()->getName() . " on a SPOON!");
                $pluginBase->getLogger()->warning("Before using the plugin you will need to open /plugins/" . $pluginBase->getDescription()->getName() . "/" . $fileToCheck . " in a text editor and agree to the terms.");
                $pluginBase->getServer()->getPluginManager()->disablePlugin($pluginBase);
                return false;
            }
        }
        return true;
    }

}