<?php

namespace bedrockcloud\cloudbridge\network\packet\response;

use bedrockcloud\cloudbridge\network\RequestPacket;

class TemplateInfoResponsePacket extends RequestPacket
{
    private string $templateName;
    private bool $isLobby;
    private bool $isPrivate;
    private bool $isBeta;
    private bool $isMaintenance;
    private int $maxPlayer;
    private bool $isStatic;
    private int $type;

    public function getPacketName(): string
    {
        return "CloudServerInfoResponsePacket";
    }

    public function handle()
    {
        $this->templateName = $this->data["templateName"];
        $this->isLobby = $this->data["isLobby"];
        $this->isPrivate = $this->data["isPrivate"];
        $this->isBeta = $this->data["isBeta"];
        $this->isMaintenance = $this->data["isMaintenance"];
        $this->maxPlayer = $this->data["maxPlayer"];
        $this->isStatic = $this->data["isStatic"];
        $this->type = $this->data["type"];
    }

    /**
     * @return int
     */
    public function getMaxPlayer(): int
    {
        return $this->maxPlayer;
    }

    /**
     * @return mixed
     */
    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->isPrivate;
    }

    /**
     * @return bool
     */
    public function isBeta(): bool
    {
        return $this->isBeta;
    }

    /**
     * @return bool
     */
    public function isLobby(): bool
    {
        return $this->isLobby;
    }

    /**
     * @return bool
     */
    public function isMaintenance(): bool
    {
        return $this->isMaintenance;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isStatic(): bool
    {
        return $this->isStatic;
    }
}