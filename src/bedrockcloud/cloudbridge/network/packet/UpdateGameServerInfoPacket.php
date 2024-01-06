<?php

namespace bedrockcloud\cloudbridge\network\packet;

use bedrockcloud\cloudbridge\network\DataPacket;
use ceepkev77\lobbyapi\objects\GameServer;

class UpdateGameServerInfoPacket extends DataPacket
{
    public int $type;
    public string $value;

    public int $TYPE_UPDATE_PLAYER_COUNT = 0;
    public int $TYPE_UPDATE_STATE_MODE = 1;


    public function getPacketName(): string
    {
       return "UpdateGameServerInfoPacket";
    }

    public function encode()
    {
        $this->addValue("packetName", $this->getPacketName());
        $this->addValue("type",$this->type);
        $this->addValue("value", $this->value);
        return parent::encode();
    }

}