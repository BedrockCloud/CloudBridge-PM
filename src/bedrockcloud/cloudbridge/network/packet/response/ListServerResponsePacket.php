<?php

namespace bedrockcloud\cloudbridge\network\packet\response;

use bedrockcloud\cloudbridge\network\RequestPacket;

class ListServerResponsePacket extends RequestPacket
{

    public function getPacketName(): string
    {
        return "ListServerResponsePacket";
    }

    public function handle()
    {
        //var_dump(json_decode($this->data["servers"], true));
        parent::handle(); // TODO: Change the autogenerated stub
    }

}