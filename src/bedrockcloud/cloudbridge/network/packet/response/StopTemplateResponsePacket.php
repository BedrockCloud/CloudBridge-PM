<?php

namespace bedrockcloud\cloudbridge\network\packet\response;

use bedrockcloud\cloudbridge\network\RequestPacket;

class StopTemplateResponsePacket extends RequestPacket {
    private bool $success = false;
    private int $failureId = -1;
    private ?string $templateName = null;

    public function getPacketName(): string
    {
        return "StopTemplateResponsePacket";
    }

    public function handle()
    {
        $this->success = $this->data["success"];
        if (!$this->success) {
            $this->failureId = $this->data["failureId"];
        } else {
            $this->templateName = $this->data["templateName"];
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
     * @return string|null
     */
    public function getTemplateName(): ?string
    {
        return $this->templateName;
    }
}