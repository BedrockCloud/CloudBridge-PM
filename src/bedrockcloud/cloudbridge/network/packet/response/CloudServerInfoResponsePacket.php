<?php

namespace bedrockcloud\cloudbridge\network\packet\response;

use bedrockcloud\cloudbridge\network\RequestPacket;

class CloudServerInfoResponsePacket extends RequestPacket
{

    private string $serverInfoName;
    private string $templateName;
    private int $state;
    private bool $isLobby;
    private bool $isBeta;
    private bool $isMaintenance;
    private bool $isStatic;
    private int $playerCount;
    private int $maxPlayer;
    public array $players = [];

    public function getPacketName(): string
    {
        return "CloudServerInfoResponsePacket";
    }

    public function handle()
    {
        $this->serverInfoName = $this->data["serverInfoName"];
        $this->templateName = $this->data["templateName"];
        $this->state = $this->data["state"];
        $this->isLobby = $this->data["isLobby"];
        $this->isBeta = $this->data["isBeta"];
        $this->isMaintenance = $this->data["isMaintenance"];
        $this->isStatic = $this->data["isStatic"];
        $this->playerCount = $this->data["playerCount"];
        $this->players = json_decode($this->data["players"], true);
        $this->maxPlayer = $this->data["maxPlayer"];
    }

    /**
     * @return array
     */
    public function getPlayers(): array
    {
        return $this->players;
    }

    /**
     * @return mixed
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @return int
     */
    public function getMaxPlayer(): int
    {
        return $this->maxPlayer;
    }

    /**
     * @return string
     */
    public function getServerInfoName(): string
    {
        return $this->serverInfoName;
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
     * @return int
     */
    public function getPlayerCount(): int
    {
        return $this->playerCount;
    }

    /**
     * @return bool
     */
    public function isMaintenance(): bool
    {
        return $this->isMaintenance;
    }

    /**
     * @return bool
     */
    public function isStatic(): bool
    {
        return $this->isStatic;
    }
}