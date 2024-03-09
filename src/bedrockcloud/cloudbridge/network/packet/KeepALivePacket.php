<?php

namespace bedrockcloud\cloudbridge\network\packet;

use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\network\DataPacket;

class KeepALivePacket extends DataPacket {

    public function handle()
    {
        CloudBridge::getInstance()->lastKeepALiveCheck = time();

        $pk = $this;
        $pk->addValue("serverName", CloudBridge::getInstance()->getServer()->getMotd());
        $this->sendPacket();
    }
}