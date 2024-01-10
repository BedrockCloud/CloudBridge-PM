<?php

namespace bedrockcloud\cloudbridge\objects;

use pocketmine\Server;
use bedrockcloud\cloudbridge\network\packet\StartServerPacket;
use bedrockcloud\cloudbridge\network\packet\UpdateGameServerInfoPacket;

class GameServer
{

    private string $name;
    private CloudGroup $cloudGroup;
    private int $state;
    private int $playerCount;
    private bool $isMaintenance;
    private bool $isBeta;
    private bool $isStatic;

    public function __construct(String $name, CloudGroup $cloudGroup)
    {
        $this->name = $name;
        $this->cloudGroup = $cloudGroup;
        $this->state = $cloudGroup->getState();
        $this->isMaintenance = $cloudGroup->isMaintenance();
        $this->isBeta = $cloudGroup->isBeta();
        $this->playerCount = 0;
        $this->isStatic = $cloudGroup->isStatic();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCloudGroup(): CloudGroup
    {
        return $this->cloudGroup;
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
            $group = $this->cloudGroup->getName();
            $count = 1;
            $pk = new StartServerPacket();
            $pk->addValue("groupName", $group);
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


}