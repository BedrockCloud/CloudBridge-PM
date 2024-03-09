<?php

namespace bedrockcloud\cloudbridge\network\packet\request;

use bedrockcloud\cloudbridge\network\RequestPacket;

class CloudPlayerInfoRequestPacket extends RequestPacket {

    public ?string $player = null;

    public function getPacketName(): string
    {
        return "CloudPlayerInfoRequestPacket";
    }

    public function encode(): bool|string
    {
        if($this->player !== null) {
            $this->addValue("playerInfoName", $this->player);
        }
        return parent::encode();
    }
}