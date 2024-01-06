<?php

namespace bedrockcloud\cloudbridge\objects;

class CloudTemplate{
    protected bool $isLobby = false;
    protected bool $isPrivate = false;
    protected bool $maintenance = false;
    protected bool $beta = false;
    protected string $name = "";
    protected int $maxPlayer = 0;

    public function __construct(string $name, bool $maintenance, bool $beta, bool $isLobby, int $maxPlayer) {
        $this->isLobby = $isLobby;
        $this->name = $name;
        $this->maintenance = $maintenance;
        $this->beta = $beta;
        $this->maxPlayer = $maxPlayer;
    }

    public function getIsLobby(): bool
    {
        return $this->isLobby;
    }

    public function getIsPrivate(): bool
    {
        return $this->isPrivate;
    }

    public function setIsLobby(bool $isLobby): void
    {
        $this->isLobby = $isLobby;
    }

    public function setIsPrivate(bool $isPrivate): void
    {
        $this->isPrivate = $isPrivate;
    }

    public function isMaintenance(): bool
    {
        return $this->maintenance;
    }

    public function isBeta(): bool
    {
        return $this->beta;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getMaxPlayer(): int
    {
        return $this->maxPlayer;
    }
}