<?php

namespace bedrockcloud\cloudbridge\utils;

use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\network\DataPacket;
use bedrockcloud\cloudbridge\network\packet\request\GameServerInfoRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\ListServerRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\ListTemplatesRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\TemplateInfoRequestPacket;
use bedrockcloud\cloudbridge\network\packet\response\GameServerInfoResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\ListServerResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\ListTemplatesResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\TemplateInfoResponsePacket;
use bedrockcloud\cloudbridge\network\packet\StartPrivateServerPacket;
use bedrockcloud\cloudbridge\objects\CloudGroup;
use bedrockcloud\cloudbridge\objects\CloudTemplate;
use bedrockcloud\cloudbridge\objects\GameServer;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class Utils{

    public static function startPrivateServer(string $group, string $serverOwner): void{
        $pk = new StartPrivateServerPacket();
        $pk->addValue("groupName", $group);
        $pk->addValue("serverOwner", $serverOwner);
        $pk->sendPacket();
    }

    public static function getPrivateServerTemplates(): array{
        $templates = [];
        foreach (CloudBridge::$cloudTemplates as $cloudTemplate){
            if ($cloudTemplate instanceof CloudTemplate){
                if ($cloudTemplate->getIsPrivate() && !isset($templates[$cloudTemplate->getName()])){
                    $templates[] = $cloudTemplate->getName();
                }
            }
        }
        return $templates;
    }

    public static function startTasks(): void{
        CloudBridge::getInstance()->getScheduler()->scheduleRepeatingTask(new class extends Task{
            public function onRun(): void
            {
                $listServers = new ListServerRequestPacket();
                $listServers->submitRequest($listServers, function (DataPacket $pk) {
                    if ($pk instanceof ListServerResponsePacket) {
                        $servers = json_decode($pk->data["servers"], true);
                        foreach ($servers as $name) {
                            $serverInfoPacket = new GameServerInfoRequestPacket();
                            $serverInfoPacket->server = $name;
                            $serverInfoPacket->submitRequest($serverInfoPacket, function (DataPacket $dataPacket) use ($name, $servers) {
                                if ($dataPacket instanceof GameServerInfoResponsePacket) {
                                    $gameServer = new GameServer($dataPacket->getServerInfoName(), new CloudGroup($dataPacket->getTemplateName(), $dataPacket->isMaintenance(), $dataPacket->isBeta(), $dataPacket->isLobby(), $dataPacket->getMaxPlayer(), $dataPacket->getState(), $dataPacket->isStatic()));
                                    $gameServer->setIsPrivate($dataPacket->isPrivate());
                                    $gameServer->setPlayerCount($dataPacket->getPlayerCount());
                                    $gameServer->setServerState($dataPacket->getState());
                                    if (!isset(CloudBridge::$gameServer[$name])) {
                                        CloudBridge::$gameServer[$name] = $gameServer;
                                    } elseif (isset(CloudBridge::$gameServer[$name])) {
                                        $gs = CloudBridge::$gameServer[$name];
                                        $gs->setIsPrivate($dataPacket->isPrivate());
                                        $gs->setPlayerCount($dataPacket->getPlayerCount());
                                        $gs->setServerState($dataPacket->getState());
                                    }
                                }

                                foreach (CloudBridge::$gameServer as $gameServer) {
                                    if (!in_array($gameServer->getName(), $servers)) {
                                        unset(CloudBridge::$gameServer[$gameServer->getName()]);
                                    }
                                }
                            });
                        }
                    }
                });
            }
        }, 20);

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
                                    $template = new CloudTemplate($dataPacket->getTemplateName(), $dataPacket->isMaintenance(), $dataPacket->isBeta(), $dataPacket->isLobby(), $dataPacket->getMaxPlayer());
                                    $template->setIsPrivate($dataPacket->isPrivate());
                                    CloudBridge::$cloudTemplates[$dataPacket->getTemplateName()] = $template;
                                }

                                foreach (CloudBridge::$cloudTemplates as $cloudTemplate) {
                                    if (!in_array($cloudTemplate->getName(), $templates)) {
                                        unset(CloudBridge::$cloudTemplates[$cloudTemplate->getName()]);
                                    }
                                }
                            });
                        }
                    }
                });
            }
        }, 20);
    }
}