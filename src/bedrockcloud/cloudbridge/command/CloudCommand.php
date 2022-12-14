<?php

namespace bedrockcloud\cloudbridge\command;

use pocketmine\Server;
use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\network\DataPacket;
use bedrockcloud\cloudbridge\network\packet\ListCloudPlayersRequestPacket;
use bedrockcloud\cloudbridge\network\packet\ListCloudPlayersResponsePacket;
use bedrockcloud\cloudbridge\network\packet\PlayerMovePacket;
use bedrockcloud\cloudbridge\network\packet\StartGroupPacket;
use bedrockcloud\cloudbridge\network\packet\StartServerPacket;
use bedrockcloud\cloudbridge\network\packet\StopGroupPacket;
use bedrockcloud\cloudbridge\network\packet\StopServerPacket;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class CloudCommand extends Command
{

    public function __construct()
    {
        parent::__construct("cloud", "BedrockCloud", false, ["cl", "bedrock", "bedrockcloud"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player) {
            if ($sender->hasPermission("cloud.admin")) {
                if (isset($args[0])) {
                    if ($args[0] == "startserver") {
                        if (isset($args[1]) && isset($args[2])) {
                            $group = $args[1];
                            $count = $args[2];
                            $pk = new StartServerPacket();
                            $pk->addValue("groupName", $group);
                            $pk->addValue("count", $count);
                            $pk->sendPacket();
                            $sender->sendMessage(CloudBridge::getPrefix() . "§aPacket was sent to the Cloud§8.");
                        } else {
                            $sender->sendMessage("/cloud startserver <group> <count>");
                        }
                    } elseif ($args[0] == "stopserver") {
                        if (isset($args[1])) {
                            $server = $args[1];
                            $pk = new StopServerPacket();
                            $pk->addValue("serverName", $server);
                            $pk->sendPacket();
                            $sender->sendMessage(CloudBridge::getPrefix() . "§aPacket was sent to the Cloud§8.");
                        } else {
                            $sender->sendMessage("/cloud stopserver <serverName>");
                        }
                    } elseif ($args[0] == "groupstop") {
                        if (isset($args[1])) {
                            $group = $args[1];
                            $pk = new StopGroupPacket();
                            $pk->addValue("groupName", $group);
                            $pk->sendPacket();
                            $sender->sendMessage(CloudBridge::getPrefix() . "§aPacket was sent to the Cloud§8.");
                        } else {
                            $sender->sendMessage("/cloud groupstop <serverName>");
                        }
                    } elseif ($args[0] == "groupstart") {
                        if (isset($args[1])) {
                            $group = $args[1];
                            $pk = new StartGroupPacket();
                            $pk->addValue("groupName", $group);
                            $pk->sendPacket();
                            $sender->sendMessage(CloudBridge::getPrefix() . "§aPacket was sent to the Cloud§8.");
                        } else {
                            $sender->sendMessage("/cloud groupstart <serverName>");
                        }
                    } elseif ($args[0] == "list") {
                        $pk = new ListCloudPlayersRequestPacket();
                        $pk->submitRequest($pk, function (DataPacket $dataPacket) use ($sender) {
                            if ($dataPacket instanceof ListCloudPlayersResponsePacket) {
                                $playerNames = $dataPacket->players;
                                sort($playerNames, SORT_STRING);
                                $sender->sendMessage("Currently are " . count($playerNames) . "/100 players online:");
                                $sender->sendMessage(implode(", ", $playerNames));
                            }
                        });
                    } elseif ($args[0] == "transfer") {
                        if (isset($args[1]) && isset($args[2])) {
                            $playerName = $args[1];
                            $server = $args[2];

                            $pk = new PlayerMovePacket();
                            $pk->playerName = $playerName;
                            $pk->toServer = $server;
                            $pk->sendPacket();
                        } else {
                            $sender->sendMessage("/cloud transfer <player> <server>");
                        }
                    } elseif ($args[0] == "version") {
                        if (CloudBridge::getVersionInfo()->identifier === "@Beta") {
                            $sender->sendMessage(CloudBridge::getPrefix() . "THE BEDROCKCLOUD IS RUNNING ON AN UNSTABLE VERSION (" . CloudBridge::getVersionInfo()->version . CloudBridge::getVersionInfo()->identifier . ")");
                        } else {
                            $sender->sendMessage(CloudBridge::getPrefix() . "THE BEDROCKCLOUD IS RUNNING ON VERSION (" . CloudBridge::getVersionInfo()->version . CloudBridge::getVersionInfo()->identifier . ")");
                        }
                    }
                } else {
                    $message = CloudBridge::getPrefix() . "§eCommands§7:" . PHP_EOL;
                    $message .= "/cloud startserver <template> <count>" . PHP_EOL;
                    $message .= "/cloud stopserver <server>" . PHP_EOL;
                    $message .= "/cloud groupstop <template>" . PHP_EOL;
                    $message .= "/cloud groupstart <template>" . PHP_EOL;
                    $message .= "/cloud transfer <player> <server>" . PHP_EOL;
                    $message .= "/cloud list" . PHP_EOL;
                    $message .= "/cloud version";

                    $sender->sendMessage($message);
                }
            } else {
                $sender->sendMessage("§cNo Perms");
            }
        }
    }
}