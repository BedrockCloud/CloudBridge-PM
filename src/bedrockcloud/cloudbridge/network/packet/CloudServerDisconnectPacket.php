<?php

namespace bedrockcloud\cloudbridge\network\packet;

use pocketmine\Server;
use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\network\DataPacket;
use pocketmine\utils\Process;

class CloudServerDisconnectPacket extends DataPacket{

    public $serverName = "";

    public function getPacketName(): string
    {
        return "CloudServerDisconnectPacket";
    }

    public function encode()
    {
        $this->addValue("packetName", $this->getPacketName());
        $this->addValue("serverName", $this->serverName);
        return parent::encode();
    }

    public function handle()
    {
        CloudBridge::getInstance()->getLogger()->notice($this->getPacketName());
        Server::getInstance()->shutdown();
        parent::handle();
    }
}