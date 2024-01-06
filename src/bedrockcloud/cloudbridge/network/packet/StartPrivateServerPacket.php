<?php

namespace bedrockcloud\cloudbridge\network\packet;

use bedrockcloud\cloudbridge\network\DataPacket;

class StartPrivateServerPacket extends DataPacket
{

    public function getPacketName(): string
    {
        return "StartPrivateServerPacket";
    }

}