<?php


namespace bedrockcloud\cloudbridge\command;

use bedrockcloud\cloudbridge\api\CloudAPI;
use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\objects\CloudTemplate;
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
        $this->setPermission("cloud.admin");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $template = CloudAPI::getInstance()->getCurrentTemplate();

        if (!$template instanceof CloudTemplate){
            $sender->sendMessage(CloudBridge::getPrefix() . "§r§f§cError whilst saving files§7!§c Template don't exists§7!");
            return;
        }

        $serverName = CloudBridge::getInstance()->getServer()->getMotd();

        if ($sender->hasPermission("cloud.admin")){
			Server::getInstance()->getCommandMap()->dispatch($sender, "save-all");

			$path1 = CloudAPI::getInstance()->getCloudPath() . "temp/". $serverName ."/";
			$path = CloudAPI::getInstance()->getCloudPath();

			Server::getInstance()->getLogger()->info("§aSave server§e " . $serverName);
			if (is_dir("{$path}templates/") && is_dir($path1)) {
				passthru("rm -r {$path}templates/" . $template->getName() . "/worlds/");
				passthru("mkdir {$path}templates/" . $template->getName() . "/worlds/");
				passthru("cp -r " . $path1 . "worlds/* {$path}templates/" . $template->getName() . "/worlds/");
                passthru("cp -r " . $path1 . "plugin_data/* {$path}templates/" . $template->getName() . "/plugin_data/");
				$sender->sendMessage(CloudBridge::getPrefix() . "§aThe Template is now updated!");
			} else {
				$sender->sendMessage(CloudBridge::getPrefix() . "§r§f§cError whilst saving files§7!§c Folder don't exists§7!");
			}
		}
    }
}