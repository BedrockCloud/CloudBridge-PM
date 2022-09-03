<?php

namespace bedrockcloud\cloudbridge\network\packet;

use bedrockcloud\cloudbridge\network\DataPacket;

class CloudPlayerAddPermissionPacket extends DataPacket
{

    public string $playerName;
    public string $permission;

    public function getPacketName(): string
    {
        return "CloudPlayerAddPermissionPacket";
    }

    public function encode()
    {
        $this->addValue("playerName", $this->playerName);
        $this->addValue("permission", $this->permission);
        return parent::encode();
    }
}