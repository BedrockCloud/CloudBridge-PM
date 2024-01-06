<?php

namespace bedrockcloud\cloudbridge\network\packet;

use bedrockcloud\cloudbridge\network\DataPacket;

class GameServerConnectPacket extends DataPacket
{

    public function getPacketName(): string
    {
        return "GameServerConnectPacket";
    }


}