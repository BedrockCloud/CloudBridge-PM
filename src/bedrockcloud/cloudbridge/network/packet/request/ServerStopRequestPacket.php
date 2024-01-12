<?php

namespace bedrockcloud\cloudbridge\network\packet\request;

use bedrockcloud\cloudbridge\network\RequestPacket;

class ServerStopRequestPacket extends RequestPacket {
    const FAILURE_SERVER_EXISTENCE = 0;

    public function getPacketName(): string
    {
        return "ServerStopRequestPacket";
    }

    public function handle()
    {
        parent::handle(); // TODO: Change the autogenerated stub
    }
}