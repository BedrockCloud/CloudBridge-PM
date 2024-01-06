<?php

namespace bedrockcloud\cloudbridge\network\packet\response;

use bedrockcloud\cloudbridge\network\RequestPacket;

class CloudPlayerInfoResponsePacket extends RequestPacket {
    private bool $success;
    private string $name;
    private string $address;
    private string $currentServer;
    private string $currentProxy;
    private string $xuid;

    public function getPacketName(): string
    {
        return "CloudPlayerInfoResponsePacket";
    }

    public function handle()
    {
        $this->success = $this->data["success"];
        if ($this->success){
            $this->name = $this->data["name"];
            $this->address = $this->data["address"];
            $this->currentServer = $this->data["currentServer"];
            $this->currentProxy = $this->data["currentProxy"];
            $this->xuid = $this->data["xuid"];
        }
    }
}