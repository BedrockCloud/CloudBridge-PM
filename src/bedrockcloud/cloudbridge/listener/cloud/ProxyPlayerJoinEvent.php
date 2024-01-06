<?php

namespace bedrockcloud\cloudbridge\listener\cloud;

use pocketmine\event\Cancellable;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

class ProxyPlayerJoinEvent extends PlayerEvent implements Cancellable
{

    private string $playerName;

    public function __construct($playerName)
    {
        $this->playerName = $playerName;
    }

    /**
     * @return string
     */
    public function getPlayerName(): string
    {
        return $this->playerName;
    }

    public function isCancelled(): bool
    {
        return false;
    }
}