<?php

namespace bedrockcloud\cloudbridge\network\packet;

use bedrockcloud\cloudbridge\network\DataPacket;

class SendToHubPacket extends DataPacket {

    public string $playerName;

    public function encode(): bool|string
    {
        $this->addValue("packetName", $this->getPacketName());
        $this->addValue("playerName", $this->playerName);
        return parent::encode();
    }
}