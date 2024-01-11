<?php

namespace bedrockcloud\cloudbridge\utils;

use bedrockcloud\cloudbridge\api\CloudAPI;
use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\network\DataPacket;
use bedrockcloud\cloudbridge\network\packet\request\CloudServerInfoRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\ListServerRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\ListTemplatesRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\TemplateInfoRequestPacket;
use bedrockcloud\cloudbridge\network\packet\response\CloudServerInfoResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\ListServerResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\ListTemplatesResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\TemplateInfoResponsePacket;
use bedrockcloud\cloudbridge\objects\CloudTemplate;
use bedrockcloud\cloudbridge\objects\CloudServer;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class Utils{
    public static function startTasks(): void{
        CloudBridge::getInstance()->getScheduler()->scheduleRepeatingTask(new class extends Task{
            public function onRun(): void
            {
                $listTemplates = new ListTemplatesRequestPacket();
                $listTemplates->submitRequest($listTemplates, function (DataPacket $pk) {
                    if ($pk instanceof ListTemplatesResponsePacket) {
                        $templates = json_decode($pk->data["templates"], true);
                        foreach ($templates as $name) {
                            $templateinfopacket = new TemplateInfoRequestPacket();
                            $templateinfopacket->server = Server::getInstance()->getMotd();
                            $templateinfopacket->submitRequest($templateinfopacket, function (DataPacket $dataPacket) use ($templates) {
                                if($dataPacket instanceof TemplateInfoResponsePacket) {
                                    $template = new CloudTemplate($dataPacket->getTemplateName(), $dataPacket->isMaintenance(), $dataPacket->isBeta(), $dataPacket->isLobby(), $dataPacket->getMaxPlayer(), $dataPacket->isStatic(), $dataPacket->getType());
                                    CloudBridge::$cloudTemplates[$dataPacket->getTemplateName()] = $template;
                                }

                                foreach (CloudBridge::$cloudTemplates as $cloudTemplate) {
                                    if (!in_array($cloudTemplate->getName(), $templates)) {
                                        unset(CloudBridge::$cloudTemplates[$cloudTemplate->getName()]);
                                    }
                                }

                                $listServers = new ListServerRequestPacket();
                                $listServers->submitRequest($listServers, function (DataPacket $pk) {
                                    if ($pk instanceof ListServerResponsePacket) {
                                        $servers = json_decode($pk->data["servers"], true);
                                        foreach ($servers as $name) {
                                            $serverInfoPacket = new CloudServerInfoRequestPacket();
                                            $serverInfoPacket->server = $name;
                                            $serverInfoPacket->submitRequest($serverInfoPacket, function (DataPacket $dataPacket) use ($name, $servers) {
                                                if ($dataPacket instanceof CloudServerInfoResponsePacket) {
                                                    $template = CloudAPI::getInstance()->getTemplate($dataPacket->getTemplateName());
                                                    $cloudServer = new CloudServer($dataPacket->getServerInfoName(), $template);
                                                    $cloudServer->setPlayerCount($dataPacket->getPlayerCount());
                                                    $cloudServer->setServerState($dataPacket->getState());
                                                    if (!isset(CloudBridge::$cloudServer[$name])) {
                                                        CloudBridge::$cloudServer[$name] = $cloudServer;
                                                    } elseif (isset(CloudBridge::$cloudServer[$name])) {
                                                        $gs = CloudBridge::$cloudServer[$name];
                                                        $gs->setPlayerCount($dataPacket->getPlayerCount());
                                                        $gs->setServerState($dataPacket->getState());
                                                    }
                                                }

                                                foreach (CloudBridge::$cloudServer as $cloudServer) {
                                                    if (!in_array($cloudServer->getName(), $servers)) {
                                                        unset(CloudBridge::$cloudServer[$cloudServer->getName()]);
                                                    }
                                                }
                                            });
                                        }
                                    }
                                });
                            });
                        }
                    }
                });
            }
        }, 20);

        CloudBridge::getInstance()->getScheduler()->scheduleRepeatingTask(new class extends Task{
            public function onRun(): void
            {
            }
        }, 20);
    }
}