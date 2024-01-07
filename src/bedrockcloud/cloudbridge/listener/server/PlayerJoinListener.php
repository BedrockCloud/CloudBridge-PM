<?php

namespace bedrockcloud\cloudbridge\listener\server;

use bedrockcloud\cloudbridge\api\CloudAPI;
use bedrockcloud\cloudbridge\network\DataPacket;
use bedrockcloud\cloudbridge\network\packet\request\CloudPlayerInfoRequestPacket;
use bedrockcloud\cloudbridge\network\packet\response\CloudPlayerInfoResponsePacket;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\network\packet\PlayerKickPacket;
use bedrockcloud\cloudbridge\network\packet\UpdateGameServerInfoPacket;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Server;

class PlayerJoinListener implements Listener
{

    /**
     * @throws \JsonException
     */
    public function onPlayerJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $name = $player->getName();

        $pk = new CloudPlayerInfoRequestPacket();
        $pk->player = $name;
        $pk->submitRequest($pk, function (DataPacket $dataPacket) use ($player, $name) {
            if ($dataPacket instanceof CloudPlayerInfoResponsePacket) {
                if (!$dataPacket->isSuccess()) {
                    if ($player instanceof Player) {
                        $player->kick("§cPlease join through the proxy");
                    }
                } else {
                    if ($player instanceof Player) {
                        $packet = new UpdateGameServerInfoPacket();
                        $packet->type = $packet->TYPE_UPDATE_PLAYER_COUNT;
                        $packet->value = count(Server::getInstance()->getOnlinePlayers());
                        $packet->sendPacket();

                        if (CloudAPI::getInstance()->getCurrentServer()->isMaintenance() && !$player->hasPermission("cloud.maintenance.join")){
                            $pk = new PlayerKickPacket();
                            $pk->playerName = $name;
                            $pk->reason = "&cThis server is currently in maintenance mode.";
                            $pk->sendPacket();
                        }

                        if ($player->hasPermission("cloud.notify")){
                            if (!is_file(CloudAPI::getInstance()->getCloudPath() . "local/notify/{$player->getName()}.json")){
                                $notifyFile = new Config(CloudAPI::getInstance()->getCloudPath() . "local/notify/{$player->getName()}.json", Config::JSON);
                                $notifyFile->set("notify", false);
                                $notifyFile->save();
                            }

                            $notifyFile = new Config(CloudAPI::getInstance()->getCloudPath() . "local/notify/{$player->getName()}.json", Config::JSON);
                            $boolToColor = ((bool)$notifyFile->get("notify")) ? "§a" : "§c";
                            $boolToText = ((bool)$notifyFile->get("notify")) ? "logged in §7to the cloud notify system." : "logged out §7of the cloud notify system.";
                            $player->sendMessage(CloudBridge::getPrefix() . "§7You are currently {$boolToColor}{$boolToText}" . PHP_EOL . "§cDo §7'§e/cloudnotify§7' §cto §alogin§8/§clogout§8.");
                        } else {
                            if (is_file(CloudAPI::getInstance()->getCloudPath() . "local/notify/{$player->getName()}.json")){
                                @unlink(CloudAPI::getInstance()->getCloudPath() . "local/notify/{$player->getName()}.json");
                            }
                        }
                    }
                }
            }
        });
    }
}