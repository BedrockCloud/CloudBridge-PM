<?php

namespace bedrockcloud\cloudbridge\api;

use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\network\packet\SendToHubPacket;
use bedrockcloud\cloudbridge\objects\CloudTemplate;
use bedrockcloud\cloudbridge\objects\CloudServer;
use bedrockcloud\cloudbridge\objects\VersionInfo;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

class CloudAPI {
    use SingletonTrait{
        reset as private;
        setInstance as private;
    }

    public function __construct()
    {
        self::setInstance($this);
    }

    /**
     * @return CloudServer|null
     */
    public function getCurrentServer(): ?CloudServer
    {
        return CloudBridge::$cloudServer[Server::getInstance()->getMotd()] ?? null;
    }

    /**
     * @param string $server
     * @return CloudServer|null
     */
    public function getServer(string $server): ?CloudServer
    {
        return CloudBridge::$cloudServer[$server] ?? null;
    }

    /**
     * @return VersionInfo
     */
    public static function getVersionInfo(): VersionInfo
    {
        return CloudBridge::$versionInfo;
    }

    public function getCurrentTemplate(): ?CloudTemplate {
        return $this->getTemplate($this->getServerProperties()->get("template"));
    }

    /**
     * @param string $template
     * @return CloudTemplate|null
     */
    public function getTemplate(string $template): ?CloudTemplate
    {
        return CloudBridge::$cloudTemplates[$template] ?? null;
    }

    public function getCloudPort(): int {
        return (int)$this->getServerProperties()->get("cloud-port");
    }

    public function getCloudPath(): string {
        return $this->getServerProperties()->get("cloud-path");
    }

    public function getServerProperties(): Config {
        return new Config(Server::getInstance()->getDataPath() . "server.properties", 0);
    }

    /**
     * @return CloudServer[]
     */
    public function getGameServers(): array{
        return CloudBridge::$cloudServer;
    }

    /**
     * @return CloudTemplate[]
     */
    public function getCloudTemplates(): array{
        return CloudBridge::$cloudTemplates;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function sendToLobby(Player $player): bool{
        if (!$this->getCurrentServer()->getTemplate()->getIsLobby()) {
            $pk = new SendToHubPacket();
            $pk->addValue("playerName", $player->getName());
            $pk->sendPacket();
            return true;
        }
        return false;
    }
}