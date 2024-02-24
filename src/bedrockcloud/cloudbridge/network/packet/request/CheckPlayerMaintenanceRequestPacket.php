<?php

namespace bedrockcloud\cloudbridge\network\packet\request;

use bedrockcloud\cloudbridge\network\RequestPacket;

class CheckPlayerMaintenanceRequestPacket extends RequestPacket {

    public ?string $player = null;

    public function getPacketName(): string
    {
        return "CheckPlayerMaintenanceRequestPacket";
    }

    public function encode()
    {
        $this->addValue("packetName", $this->getPacketName());
        if($this->player !== null) {
            $this->addValue("playerInfoName", $this->player);
        }
        return parent::encode();
    }
}