<?php

namespace bedrockcloud\cloudbridge\network\packet\response;

use bedrockcloud\cloudbridge\network\RequestPacket;

class ServerStartResponsePacket extends RequestPacket {
    private bool $success;
    private int $failureId = -1;
    private array $servers = [];

    public function handle(): void
    {
        $this->success = (bool)$this->data["success"];
        if (!$this->success) {
            $this->failureId = $this->data["failureId"];
        } else {
            $this->servers = json_decode($this->data["servers"], true);
        }
        parent::handle(); // TODO: Change the autogenerated stub
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return int
     */
    public function getFailureId(): int
    {
        return $this->failureId;
    }

    /**
     * @return array
     */
    public function getServers(): array
    {
        return $this->servers;
    }
}