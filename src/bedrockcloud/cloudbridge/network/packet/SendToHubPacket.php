<?php

namespace bedrockcloud\cloudbridge\network\packet;

use bedrockcloud\cloudbridge\network\DataPacket;

class SendToHubPacket extends DataPacket
{

    public string $playerName;

    public function getPacketName(): string
    {
        return "SendToHubPacket";
    }

    public function encode()
    {
        $this->addValue("playerName", $this->playerName);
        return parent::encode();
    }
}