<?php

namespace bedrockcloud\cloudbridge\network\packet\request;

use bedrockcloud\cloudbridge\network\RequestPacket;

class TemplateInfoRequestPacket extends RequestPacket {

    public ?string $server = null;

    public function encode(): bool|string
    {
        if($this->server !== null) {
            $this->addValue("serverName", $this->server);
        }
        return parent::encode();
    }
}