<?php

namespace bedrockcloud\cloudbridge\command;

use bedrockcloud\cloudbridge\api\CloudAPI;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use bedrockcloud\cloudbridge\CloudBridge;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class CloudNotifyCommand extends Command
{

    public function __construct()
    {
        parent::__construct("cloudnotify", "Cloud notify command", false, []);
        $this->setPermission("cloud.notify");
    }

    /**
     * @throws \JsonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$sender instanceof Player) return;
        if (!$this->testPermissionSilent($sender)) return;
        $notifyFile = new Config(CloudAPI::getInstance()->getCloudPath() . "local/notify/{$sender->getName()}.json", Config::JSON);
        if (!(bool)$notifyFile->get("notify")){
            $notifyFile->set("notify", true);
            $notifyFile->save();
            $sender->sendMessage(CloudBridge::getPrefix() . "§aYou are now logged in to the cloud notify system§7.");
        } else {
            $notifyFile->set("notify", false);
            $notifyFile->save();
            $sender->sendMessage(CloudBridge::getPrefix() . "§cYou are no longer logged in to the cloud notify system§7.");
        }
    }
}