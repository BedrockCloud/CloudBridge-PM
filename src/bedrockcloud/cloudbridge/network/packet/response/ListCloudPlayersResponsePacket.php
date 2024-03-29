<?php

namespace bedrockcloud\cloudbridge\network\packet\response;

use bedrockcloud\cloudbridge\network\RequestPacket;

class ListCloudPlayersResponsePacket extends RequestPacket
{

    public array $players = [];

    public function getPacketName(): string
    {
        return "ListCloudPlayersResponsePacket";
    }

    public function handle()
    {
        $this->players = json_decode($this->data["players"], true);
        parent::handle(); // TODO: Change the autogenerated stub
    }

}