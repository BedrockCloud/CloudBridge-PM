<?php

namespace bedrockcloud\cloudbridge\command;

use bedrockcloud\cloudbridge\api\CloudAPI;
use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\network\DataPacket;
use bedrockcloud\cloudbridge\network\packet\PlayerMovePacket;
use bedrockcloud\cloudbridge\network\packet\request\ListCloudPlayersRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\ServerStartRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\ServerStopRequestPacket;
use bedrockcloud\cloudbridge\network\packet\response\ListCloudPlayersResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\ServerStartResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\ServerStopResponsePacket;
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
        $this->setPermission("cloud.admin");
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
                            $pk = new ServerStartRequestPacket();
                            $pk->addValue("groupName", $group);
                            $pk->addValue("count", $count);
                            $pk->submitRequest($pk, function (DataPacket $dataPacket) use ($sender, $pk){
                               if ($dataPacket instanceof ServerStartResponsePacket) {
                                   if ($dataPacket->isSuccess()) {
                                       $count = count($dataPacket->getServers());
                                       $sender->sendMessage(CloudBridge::getPrefix() . "§aStarted §e{$count} §aservers successfully§7.");
                                   } else {
                                       if ($dataPacket->getFailureId() == $pk::FAILURE_GROUP_RUNNING) {
                                           $sender->sendMessage(CloudBridge::getPrefix() . "§cThis group isn't running.");
                                       } else if ($dataPacket->getFailureId() == $pk::FAILURE_TEMPLATE_EXISTENCE) {
                                           $sender->sendMessage(CloudBridge::getPrefix() . "§cThis template don't exists.");
                                       }
                                   }
                               }
                            });
                        } else {
                            $sender->sendMessage("/cloud startserver <group> <count>");
                        }
                    } elseif ($args[0] == "stopserver") {
                        if (isset($args[1])) {
                            $server = $args[1];
                            $pk = new ServerStopRequestPacket();
                            $pk->addValue("serverName", $server);
                            $pk->submitRequest($pk, function (DataPacket $dataPacket) use ($sender, $pk){
                                if ($dataPacket instanceof ServerStopResponsePacket) {
                                    if ($dataPacket->isSuccess()) {
                                        $sender->sendMessage(CloudBridge::getPrefix() . "§aYou have stopped the server §e{$dataPacket->getServer()} §asuccessfully§7.");
                                    } else {
                                        if ($dataPacket->getFailureId() == $pk::FAILURE_SERVER_EXISTENCE) {
                                            $sender->sendMessage(CloudBridge::getPrefix() . "§cThis server don't exists.");
                                        }
                                    }
                                }
                            });
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
                        if (CloudAPI::getVersionInfo()->identifier === "@Beta") {
                            $sender->sendMessage(CloudBridge::getPrefix() . "THE BEDROCKCLOUD IS RUNNING ON AN UNSTABLE VERSION (" . CloudAPI::getVersionInfo()->version . CloudAPI::getVersionInfo()->identifier . ")");
                        } else {
                            $sender->sendMessage(CloudBridge::getPrefix() . "THE BEDROCKCLOUD IS RUNNING ON VERSION (" . CloudAPI::getVersionInfo()->version . CloudAPI::getVersionInfo()->identifier . ")");
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