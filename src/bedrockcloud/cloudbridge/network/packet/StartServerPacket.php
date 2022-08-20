<?php

namespace bedrockcloud\cloudbridge\network\packet;

use bedrockcloud\cloudbridge\network\DataPacket;

class StartServerPacket extends DataPacket
{

    public function getPacketName(): string
    {
        return "StartServerPacket";
    }

}