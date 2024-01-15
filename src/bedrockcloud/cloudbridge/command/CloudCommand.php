<?php

namespace bedrockcloud\cloudbridge\command;

use bedrockcloud\cloudbridge\api\CloudAPI;
use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\network\DataPacket;
use bedrockcloud\cloudbridge\network\packet\PlayerMovePacket;
use bedrockcloud\cloudbridge\network\packet\request\ListCloudPlayersRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\SaveServerRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\ServerStartRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\ServerStopRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\StartTemplateRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\StopTemplateRequestPacket;
use bedrockcloud\cloudbridge\network\packet\response\ListCloudPlayersResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\SaveServerResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\ServerStartResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\ServerStopResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\StartTemplateResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\StopTemplateResponsePacket;
use bedrockcloud\cloudbridge\network\packet\StartGroupPacket;
use bedrockcloud\cloudbridge\network\packet\StopGroupPacket;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;

class CloudCommand extends Command
{

    public function __construct()
    {
        parent::__construct("cloud", "Main cloud command", false, []);
        $this->setPermission("cloud.admin");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $helpMessage = CloudBridge::getPrefix() . "§eCommands§7:" . PHP_EOL;
        $helpMessage .= "/cloud start <template|server> <template> [count]" . PHP_EOL;
        $helpMessage .= "/cloud stop <template|server> <name>" . PHP_EOL;
        $helpMessage .= "/cloud transfer <player> <server>" . PHP_EOL;
        $helpMessage .= "/cloud save" . PHP_EOL;
        $helpMessage .= "/cloud list" . PHP_EOL;
        $helpMessage .= "/cloud version";

        if ($sender instanceof Player) {
            if ($sender->hasPermission("cloud.admin")) {
                if (isset($args[0])) {
                    if ($args[0] == "start") {
                        if (isset($args[1])) {
                            if (strtolower($args[1]) === "server") {
                                if (isset($args[2]) && isset($args[3])) {
                                    $template = $args[2];
                                    $count = $args[3];
                                    $pk = new ServerStartRequestPacket();
                                    $pk->addValue("templateName", $template);
                                    $pk->addValue("count", $count);
                                    $pk->submitRequest($pk, function (DataPacket $dataPacket) use ($sender, $pk) {
                                        if ($dataPacket instanceof ServerStartResponsePacket) {
                                            if ($dataPacket->isSuccess()) {
                                                $count = count($dataPacket->getServers());
                                                $sender->sendMessage(CloudBridge::getPrefix() . "§aStarted §e{$count} §aservers successfully§7.");
                                            } else {
                                                if ($dataPacket->getFailureId() == $pk::FAILURE_TEMPLATE_RUNNING) {
                                                    $sender->sendMessage(CloudBridge::getPrefix() . "§cThis template isn't running.");
                                                } else if ($dataPacket->getFailureId() == $pk::FAILURE_TEMPLATE_EXISTENCE) {
                                                    $sender->sendMessage(CloudBridge::getPrefix() . "§cThis template don't exists.");
                                                }
                                            }
                                        }
                                    });
                                }
                            } elseif (strtolower($args[1]) === "template") {
                                if (isset($args[2])) {
                                    $template = $args[2];
                                    $pk = new StartTemplateRequestPacket();
                                    $pk->addValue("templateName", $template);
                                    $pk->submitRequest($pk, function (DataPacket $dataPacket) use ($sender, $pk) {
                                        if ($dataPacket instanceof StartTemplateResponsePacket) {
                                            if ($dataPacket->isSuccess()) {
                                                $template = $dataPacket->getTemplate()[0];
                                                $sender->sendMessage(CloudBridge::getPrefix() . "§aThe template §e{$template} §awas started succesfully§7.");
                                            } else {
                                                if ($dataPacket->getFailureId() === $pk::FAILURE_TEMPLATE_EXISTENCE) {
                                                    $sender->sendMessage(CloudBridge::getPrefix() . "§cThis template don't exists.");
                                                } elseif ($dataPacket->getFailureId() === $pk::FAILURE_TEMPLATE_RUNNING) {
                                                    $sender->sendMessage(CloudBridge::getPrefix() . "§cThis template is already running.");
                                                }
                                            }
                                        }
                                    });
                                }
                            } else {
                                $sender->sendMessage("/cloud start <template|server> <template> [count]");
                            }
                        } else {
                            $sender->sendMessage("/cloud start <template|server> <template> [count]");
                        }
                    } elseif ($args[0] == "stop") {
                        if (isset($args[1]) && isset($args[2])) {
                            if (strtolower($args[1]) === "server") {
                                $server = $args[2];
                                $pk = new ServerStopRequestPacket();
                                $pk->addValue("serverName", $server);
                                $pk->submitRequest($pk, function (DataPacket $dataPacket) use ($sender, $pk) {
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
                            } elseif (strtolower($args[1]) === "template") {
                                $template = $args[2];
                                $pk = new StopTemplateRequestPacket();
                                $pk->addValue("templateName", $template);
                                $pk->submitRequest($pk, function (DataPacket $dataPacket) use ($sender, $pk) {
                                    if ($dataPacket instanceof StopTemplateResponsePacket) {
                                        if ($dataPacket->isSuccess()) {
                                            $template = $dataPacket->getTemplateName();
                                            $sender->sendMessage(CloudBridge::getPrefix() . "§aThe template §e{$template} §awas stopped succesfully§7.");
                                        } else {
                                            if ($dataPacket->getFailureId() === $pk::FAILURE_TEMPLATE_EXISTENCE) {
                                                $sender->sendMessage(CloudBridge::getPrefix() . "§cThis template don't exists.");
                                            } elseif ($dataPacket->getFailureId() === $pk::FAILURE_TEMPLATE_NOT_RUNNING) {
                                                $sender->sendMessage(CloudBridge::getPrefix() . "§cThis template isn't running.");
                                            }
                                        }
                                    }
                                });
                            } else {
                                $sender->sendMessage("/cloud stop <template|server> <name>");
                            }
                        } else {
                            $sender->sendMessage("/cloud stop <template|server> <name>");
                        }
                    } elseif ($args[0] == "list") {
                        $pk = new ListCloudPlayersRequestPacket();
                        $pk->submitRequest($pk, function (DataPacket $dataPacket) use ($sender) {
                            if ($dataPacket instanceof ListCloudPlayersResponsePacket) {
                                $playerNames = $dataPacket->players;
                                sort($playerNames, SORT_STRING);
                                $sender->sendMessage("Currently are " . count($playerNames) . " players online:");
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
                    } elseif ($args[0] == "save") {
                        Server::getInstance()->getCommandMap()->dispatch($sender, "save-all");
                        $serverName = CloudBridge::getInstance()->getServer()->getMotd();
                        $pk = new SaveServerRequestPacket();
                        $pk->addValue("serverName", $serverName);
                        $pk->submitRequest($pk, function (DataPacket $dataPacket) use ($pk, $sender, $serverName) {
                            if ($dataPacket instanceof SaveServerResponsePacket) {
                                if ($dataPacket->isSuccess()) {
                                    $sender->sendMessage(CloudBridge::getPrefix() . "§aYou have saved the server §e{$serverName} §asuccessfully§7.");
                                } else {
                                    if ($dataPacket->getFailureId() === $pk::FAILURE_SERVER_EXISTENCE) {
                                        $sender->sendMessage(CloudBridge::getPrefix() . "§cThis server don't exists.");
                                    }
                                }
                            }
                        });
                    } else {
                        $sender->sendMessage($helpMessage);
                    }
                } else {
                    $sender->sendMessage($helpMessage);
                }
            } else {
                $sender->sendMessage("§cNo Perms");
            }
        }
    }
}