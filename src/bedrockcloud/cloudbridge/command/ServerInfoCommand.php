<?php

namespace bedrockcloud\cloudbridge\command;

use bedrockcloud\cloudbridge\api\CloudAPI;
use bedrockcloud\cloudbridge\CloudBridge;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use bedrockcloud\cloudbridge\objects\GameServerState;

class ServerInfoCommand extends Command
{

    public function __construct()
    {
        parent::__construct("serverinfo", "ServerInfo Command - BedrockCloud", false, []);
        $this->setPermission("cloud.serverinfo");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(
                "§7---------------§r" . PHP_EOL .
                "ServerName: " . CloudAPI::getInstance()->getCurrentServer()->getName() . PHP_EOL .
                "TemplateName: " . CloudAPI::getInstance()->getCurrentServer()->getCloudGroup()->getName() . PHP_EOL .
                "Mode: " . GameServerState::intToString(CloudAPI::getInstance()->getCurrentServer()->getState()) . PHP_EOL .
                "§rPlayers: " . CloudAPI::getInstance()->getCurrentServer()->getPlayerCount() . PHP_EOL .
                "§7---------------§r"
            );
        } else {
            if (($gameServer = CloudAPI::getInstance()->getGameServer($args[0])) != null){
                $sender->sendMessage(
                    "§7---------------§r" . PHP_EOL .
                    "ServerName: " . $gameServer->getName() . PHP_EOL .
                    "TemplateName: " . $gameServer->getCloudGroup()->getName() . PHP_EOL .
                    "Mode: " . GameServerState::intToString($gameServer->getState()) . PHP_EOL .
                    "§rPlayers: " . $gameServer->getPlayerCount() . PHP_EOL .
                    "§7---------------§r"
                );
            } else {
                $sender->sendMessage(CloudBridge::getPrefix() . "§cThis server don't exists.");
            }
        }
    }

}