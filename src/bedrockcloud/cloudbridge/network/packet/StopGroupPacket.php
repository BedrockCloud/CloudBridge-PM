<?php

namespace bedrockcloud\cloudbridge\network\packet;


use bedrockcloud\cloudbridge\network\DataPacket;

class StopGroupPacket extends DataPacket
{

    public function getPacketName(): string
    {
        return "StopGroupPacket";
    }
}