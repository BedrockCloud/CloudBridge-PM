<?php

namespace bedrockcloud\cloudbridge\network\packet\request;

use bedrockcloud\cloudbridge\network\RequestPacket;

class CloudServerInfoRequestPacket extends RequestPacket {

    public ?string $server = null;

    public function encode(): bool|string
    {
        if($this->server !== null) {
            $this->addValue("serverInfoName", $this->server);
        }
        return parent::encode();
    }
}