<?php

namespace bedrockcloud\cloudbridge\listener\server;

use Core\Core\api\ServerAPI;
use pocketmine\utils\Config;
use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\listener\cloud\ProxyPlayerJoinEvent;
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
    public function onPlayerJoin(PlayerJoinEvent $event)
    {

        $player = $event->getPlayer();

        //Update Server Stats
        $packet = new UpdateGameServerInfoPacket();
        $packet->type = $packet->TYPE_UPDATE_PLAYER_COUNT;
        $packet->value = count(Server::getInstance()->getOnlinePlayers());
        $packet->sendPacket();

        if (CloudBridge::getGameServer()->isMaintenance() && !$event->getPlayer()->hasPermission("cloud.maintenance.join")){
            $pk = new PlayerKickPacket();
            $pk->playerName = $event->getPlayer()->getName();
            $pk->reason = "&cThis server is currently in maintenance mode.";
            $pk->sendPacket();
        }

        if (CloudBridge::getGameServer()->isBeta()){
            $event->getPlayer()->sendMessage(CloudBridge::getPrefix() . "§cThis server is currently in §l§6BETA MODE§r§8." . PHP_EOL .
            "§4There may be errors or it may be that some things have not yet been translated§8.");
        }

        if ($player->hasPermission("cloud.notify")){
            if (!is_file(CloudBridge::getInstance()->getCloudPath() . "local/notify/{$player->getName()}.json")){
                $notifyFile = new Config(CloudBridge::getInstance()->getCloudPath() . "local/notify/{$player->getName()}.json", Config::JSON);
                $notifyFile->set("notify", false);
                $notifyFile->save();
            }

            $notifyFile = new Config(CloudBridge::getInstance()->getCloudPath() . "local/notify/{$player->getName()}.json", Config::JSON);
            $boolToColor = ((bool)$notifyFile->get("notify")) ? "§a" : "§c";
            $boolToText = ((bool)$notifyFile->get("notify")) ? "logged in §7to the cloud notify system." : "logged out §7of the cloud notify system.";
            $player->sendMessage(CloudBridge::getPrefix() . "§7You are currently {$boolToColor}{$boolToText}" . PHP_EOL . "§cDo §7'§e/cloudnotify§7' §cto §alogin§8/§clogout§8.");
        } else {
            if (is_file(CloudBridge::getInstance()->getCloudPath() . "local/notify/{$player->getName()}.json")){
                @unlink(CloudBridge::getInstance()->getCloudPath() . "local/notify/{$player->getName()}.json");
            }
        }
    }
}