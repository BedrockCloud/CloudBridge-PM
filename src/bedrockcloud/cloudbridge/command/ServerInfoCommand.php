<?php

namespace bedrockcloud\cloudbridge\command;

use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\network\packet\CloudPlayerAddPermissionPacket;
use bedrockcloud\cloudbridge\network\packet\UpdateGameServerInfoPacket;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\Server;
use bedrockcloud\cloudbridge\objects\GameServerState;

class ServerInfoCommand extends Command
{

    public function __construct()
    {
        parent::__construct("serverinfo", "ServerInfo Command - BedrockCloud", false, []);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(
                "§7---------------§r" . PHP_EOL .
                "ServerName: " . CloudBridge::getGameServer()->getName() . PHP_EOL .
                "TemplateName: " . CloudBridge::getGameServer()->getCloudGroup()->getName() . PHP_EOL .
                "Mode: " . GameServerState::intToString(CloudBridge::getGameServer()->getState()) . PHP_EOL .
                "§rPlayers: " . CloudBridge::getGameServer()->getPlayerCount() . PHP_EOL .
                "§7---------------§r"
            );
        } else {
            if (isset(CloudBridge::$gameServer[$args[0]])){
                $gameServer = CloudBridge::$gameServer[$args[0]];
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