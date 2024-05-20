<?php

namespace bedrockcloud\cloudbridge\network\packet\request;

use bedrockcloud\cloudbridge\network\RequestPacket;

class CheckPlayerMaintenanceRequestPacket extends RequestPacket {

    public ?string $player = null;

    public function encode(): bool|string
    {
        if($this->player !== null) {
            $this->addValue("playerInfoName", $this->player);
        }
        return parent::encode();
    }
}