<?php

namespace bedrockcloud\cloudbridge\network\packet;

use bedrockcloud\cloudbridge\network\DataPacket;

class PlayerKickPacket extends DataPacket
{
    public string $playerName;
    public string $reason;

    public function encode(): bool|string
    {
        $this->addValue("playerName", $this->playerName);
        $this->addValue("reason", $this->reason);
        return parent::encode(); // TODO: Change the autogenerated stub
    }

}