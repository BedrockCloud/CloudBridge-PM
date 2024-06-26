<?php

namespace bedrockcloud\cloudbridge\network\packet\response;

use bedrockcloud\cloudbridge\network\RequestPacket;

class ListServerResponsePacket extends RequestPacket {

    public array $servers = [];

    public function handle(): void
    {
        $this->servers = json_decode($this->data["servers"], true);
        parent::handle(); // TODO: Change the autogenerated stub
    }

}