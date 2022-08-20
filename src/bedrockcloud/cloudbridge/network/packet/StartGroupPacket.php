<?php
namespace bedrockcloud\cloudbridge\network\packet;



use bedrockcloud\cloudbridge\network\DataPacket;

class StartGroupPacket extends DataPacket
{

    public function getPacketName(): string
    {
        return "StartGroupPacket";
    }



}