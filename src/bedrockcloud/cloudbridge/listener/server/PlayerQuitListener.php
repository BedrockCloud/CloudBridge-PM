<?php

namespace bedrockcloud\cloudbridge\listener\server;

use bedrockcloud\cloudbridge\network\packet\UpdateGameServerInfoPacket;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Server;

class PlayerQuitListener implements Listener
{

    public function onQuit(PlayerQuitEvent $event){
        $packet = new UpdateGameServerInfoPacket();
        $packet->type = $packet->TYPE_UPDATE_PLAYER_COUNT;
        $packet->value = (count(Server::getInstance()->getOnlinePlayers()) -1);
        $packet->sendPacket();
    }
}