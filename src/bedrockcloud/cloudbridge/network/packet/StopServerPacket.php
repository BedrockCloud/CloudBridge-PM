<?php

namespace bedrockcloud\cloudbridge\network\packet;

use bedrockcloud\cloudbridge\network\DataPacket;

class StopServerPacket extends DataPacket
{

    public function getPacketName(): string
    {
        return "StopServerPacket";
    }

}