<?php

namespace bedrockcloud\cloudbridge\network\packet;

use pocketmine\Server;
use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\network\DataPacket;
use pocketmine\utils\Process;

class CloudServerDisconnectPacket extends DataPacket {

    public string $serverName = "";

    public function encode(): bool|string
    {
        $this->addValue("packetName", $this->getPacketName());
        $this->addValue("serverName", $this->serverName);
        return parent::encode();
    }

    public function handle(): void
    {
        CloudBridge::getInstance()->getLogger()->notice($this->getPacketName());
        Server::getInstance()->shutdown();
        parent::handle();
    }
}