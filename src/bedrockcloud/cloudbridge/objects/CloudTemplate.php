<?php

namespace bedrockcloud\cloudbridge\objects;

class CloudTemplate{
    protected bool $isLobby = false;
    protected bool $maintenance = false;
    protected bool $beta = false;
    protected bool $static = false;
    protected string $name = "";
    protected int $maxPlayer = 0;
    protected int $type = 0;

    public function __construct(string $name, bool $maintenance, bool $beta, bool $isLobby, int $maxPlayer, bool $static, int $type) {
        $this->isLobby = $isLobby;
        $this->name = $name;
        $this->maintenance = $maintenance;
        $this->beta = $beta;
        $this->maxPlayer = $maxPlayer;
        $this->static = $static;
        $this->type = $type;
    }

    public function getIsLobby(): bool
    {
        return $this->isLobby;
    }

    public function setIsLobby(bool $isLobby): void
    {
        $this->isLobby = $isLobby;
    }

    public function isMaintenance(): bool
    {
        return $this->maintenance;
    }

    /**
     * @param bool $maintenance
     */
    public function setMaintenance(bool $maintenance): void
    {
        $this->maintenance = $maintenance;
    }

    public function isBeta(): bool
    {
        return $this->beta;
    }

    /**
     * @param bool $beta
     */
    public function setBeta(bool $beta): void
    {
        $this->beta = $beta;
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

    /**
     * @param int $maxPlayer
     */
    public function setMaxPlayer(int $maxPlayer): void
    {
        $this->maxPlayer = $maxPlayer;
    }

    /**
     * @return bool
     */
    public function isStatic(): bool
    {
        return $this->static;
    }

    /**
     * @param bool $static
     */
    public function setStatic(bool $static): void
    {
        $this->static = $static;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }
}