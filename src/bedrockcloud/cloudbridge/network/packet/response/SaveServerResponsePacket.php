<?php

namespace bedrockcloud\cloudbridge\network\packet\response;

use bedrockcloud\cloudbridge\network\RequestPacket;

class SaveServerResponsePacket extends RequestPacket {
    private bool $success = false;
    private int $failureId = -1;

    public function getPacketName(): string
    {
        return "SaveServerResponsePacket";
    }

    public function handle()
    {
        $this->success = $this->data["success"];
        if (!$this->success) {
            $this->failureId = $this->data["failureId"];
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
     * @return int
     */
    public function getFailureId(): int
    {
        return $this->failureId;
    }
}