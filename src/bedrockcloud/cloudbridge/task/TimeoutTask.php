<?php

namespace bedrockcloud\cloudbridge\task;

use bedrockcloud\cloudbridge\api\CloudAPI;
use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\objects\CloudServerState;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class TimeoutTask extends Task {

    public function onRun(): void
    {
        $server = CloudAPI::getInstance()->getCurrentServer();
        if ($server->getState() === CloudServerState::NOT_REGISTERED) return;
        if ((CloudBridge::getInstance()->lastKeepALiveCheck + 10) <= time()) {
            Server::getInstance()->getLogger()->warning("§cCloud connection timed out.");
            Server::getInstance()->shutdown();
        }
    }
}