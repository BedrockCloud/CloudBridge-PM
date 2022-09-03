<?php

namespace bedrockcloud\cloudbridge\network\packet;

use bedrockcloud\cloudbridge\network\RequestPacket;

class GameServerInfoRequestPacket extends RequestPacket {

    public ?string $server = null;

    public function getPacketName(): string
    {
        return "GameServerInfoRequestPacket";
    }

    public function encode()
    {
        if($this->server !== null) {
            $this->addValue("serverInfoName", $this->server);
        }
        return parent::encode();
    }

}