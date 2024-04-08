<?php

namespace bedrockcloud\cloudbridge\network\packet\response;

use bedrockcloud\cloudbridge\network\RequestPacket;

class CheckPlayerMaintenanceResponsePacket extends RequestPacket {
    private bool $success;
    private string $name;

    public function handle(): void
    {
        $this->success = $this->data["success"];
        if ($this->success){
            $this->name = $this->data["name"];
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
}