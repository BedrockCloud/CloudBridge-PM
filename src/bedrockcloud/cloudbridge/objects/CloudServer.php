<?php

namespace bedrockcloud\cloudbridge\objects;

use bedrockcloud\cloudbridge\network\packet\request\ServerStartRequestPacket;
use pocketmine\Server;
use bedrockcloud\cloudbridge\network\packet\StartServerPacket;
use bedrockcloud\cloudbridge\network\packet\UpdateGameServerInfoPacket;

class CloudServer
{

    private string $name;
    private CloudTemplate $cloudTemplate;
    private int $state;
    private int $playerCount;
    private bool $isMaintenance;
    private bool $isBeta;
    private bool $isStatic;
    private array $customServerData;

    public function __construct(String $name, CloudTemplate $cloudTemplate)
    {
        $this->name = $name;
        $this->cloudTemplate = $cloudTemplate;
        $this->state = CloudServerState::NOT_REGISTERED;
        $this->isMaintenance = $cloudTemplate->isMaintenance();
        $this->isBeta = $cloudTemplate->isBeta();
        $this->playerCount = 0;
        $this->isStatic = $cloudTemplate->isStatic();
        $this->customServerData = [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTemplate(): CloudTemplate
    {
        return $this->cloudTemplate;
    }

    /**
     * @return bool
     */
    public function isBeta(): bool
    {
        return $this->isBeta;
    }

    public function getState(): int
    {
        return $this->state ?? 0;
    }

    /**
     * @return bool
     */
    public function isMaintenance(): bool
    {
        return $this->isMaintenance;
    }

    public function setState(int $state, bool $startNewServer = false): void
    {
        $this->state = $state;
        if ($startNewServer){
            $template = $this->cloudTemplate->getName();
            $count = 1;
            $pk = new ServerStartRequestPacket();
            $pk->addValue("templateName", $template);
            $pk->addValue("count", $count);
            $pk->sendPacket();
            Server::getInstance()->getLogger()->info("§cStarted an new CloudGameServer.");
        }

        $packet = new UpdateGameServerInfoPacket();
        $packet->type = $packet->TYPE_UPDATE_STATE_MODE;
        $packet->value = $state;
        $packet->sendPacket();
    }

    public function setServerState(int $state){
        $this->state = $state;
    }


    public function setIsMaintenance(bool $isMaintenance): void
    {
        $this->isMaintenance = $isMaintenance;
    }

    /**
     * @param bool $isBeta
     */
    public function setIsBeta(bool $isBeta): void
    {
        $this->isBeta = $isBeta;
    }

    /**
     * @return int
     */
    public function getPlayerCount(): int
    {
        return $this->playerCount;
    }

    /**
     * @param int $playerCount
     */
    public function setPlayerCount(int $playerCount): void
    {
        $this->playerCount = $playerCount;
    }

    /**
     * @return bool
     */
    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    /**
     * @return array
     */
    public function getCustomServerData(): array
    {
        return $this->customServerData;
    }

    /**
     * @param array $customServerData
     */
    public function setCustomServerData(array $customServerData): void
    {
        $this->customServerData = $customServerData;
    }
}