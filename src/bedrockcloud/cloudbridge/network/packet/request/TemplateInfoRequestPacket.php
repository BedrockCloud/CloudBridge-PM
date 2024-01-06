<?php

namespace bedrockcloud\cloudbridge\network\packet\request;

use bedrockcloud\cloudbridge\network\RequestPacket;

class TemplateInfoRequestPacket extends RequestPacket {

    public ?string $server = null;

    public function getPacketName(): string
    {
        return "TemplateInfoRequestPacket";
    }

    public function encode()
    {
        $this->addValue("packetName", $this->getPacketName());
        if($this->server !== null) {
            $this->addValue("serverName", $this->server);
        }
        return parent::encode();
    }
}