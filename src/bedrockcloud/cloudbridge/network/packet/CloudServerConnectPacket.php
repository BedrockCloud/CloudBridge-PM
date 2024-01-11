<?php

namespace bedrockcloud\cloudbridge\network\packet;

use bedrockcloud\cloudbridge\network\DataPacket;

class CloudServerConnectPacket extends DataPacket
{

    public function getPacketName(): string
    {
        return "CloudServerConnectPacket";
    }


}