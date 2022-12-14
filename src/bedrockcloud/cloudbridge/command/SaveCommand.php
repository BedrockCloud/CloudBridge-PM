<?php


namespace bedrockcloud\cloudbridge\command;

use bedrockcloud\cloudbridge\CloudBridge;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandException;
use pocketmine\Server;
use pocketmine\utils\MainLogger;

class SaveCommand extends Command
{

    public function __construct()
    {
        parent::__construct("save", "Save GameServer");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $template = CloudBridge::getInstance()->getTemplate();
        $serverName = CloudBridge::getInstance()->getServer()->getMotd();

        if ($sender->hasPermission("cloud.admin")){
			Server::getInstance()->getCommandMap()->dispatch($sender, "save-all");

			$path1 = CloudBridge::getInstance()->getCloudPath() . "temp/". $serverName ."/";
			$path = CloudBridge::getInstance()->getCloudPath();

			Server::getInstance()->getLogger()->info("§aSave server§e " . $serverName);
			if (is_dir("{$path}templates/") && is_dir($path1)) {
				passthru("rm -r {$path}templates/" . $template . "/worlds/");
				passthru("mkdir {$path}templates/" . $template . "/worlds/");
				passthru("cp -r " . $path1 . "worlds/* {$path}templates/" . $template . "/worlds/");
                passthru("cp -r " . $path1 . "plugin_data/* {$path}templates/" . $template . "/plugin_data/");
				$sender->sendMessage(CloudBridge::getPrefix() . "§aThe Template is now updated!");
			} else {
				$sender->sendMessage("§b§lCloud §8x §r§f§cError whilst saving files§7!§c Folder don't exists§7!");
			}
		}
    }
}