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

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getCurrentServer(): string
    {
        return $this->currentServer;
    }

    /**
     * @return string
     */
    public function getCurrentProxy(): string
    {
        return $this->currentProxy;
    }

    /**
     * @return string
     */
    public function getXuid(): string
    {
        return $this->xuid;
    }
}