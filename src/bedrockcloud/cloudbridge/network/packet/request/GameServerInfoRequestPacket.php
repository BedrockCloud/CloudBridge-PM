<?php

namespace bedrockcloud\cloudbridge\network\packet\request;

use bedrockcloud\cloudbridge\network\RequestPacket;

class GameServerInfoRequestPacket extends RequestPacket {

    public ?string $server = null;

    public function getPacketName(): string
    {
        return "GameServerInfoRequestPacket";
    }

    public function encode()
    {
        $this->addValue("packetName", $this->getPacketName());
        if($this->server !== null) {
            $this->addValue("serverInfoName", $this->server);
        }
        return parent::encode();
    }
}