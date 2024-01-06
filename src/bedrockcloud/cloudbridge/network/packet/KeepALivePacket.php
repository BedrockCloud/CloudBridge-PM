<?php

namespace bedrockcloud\cloudbridge\network\packet;

use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\network\DataPacket;

class KeepALivePacket extends DataPacket
{
    public function getPacketName(): string
    {
        return "KeepALivePacket";
    }

    public function handle()
    {
        $pk = $this;
        $pk->addValue("packetName", $this->getPacketName());
        $pk->addValue("serverName", CloudBridge::getInstance()->getServer()->getMotd());
        $this->sendPacket();
    }
}