<?php

namespace bedrockcloud\cloudbridge\api;

use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\objects\CloudTemplate;
use bedrockcloud\cloudbridge\objects\GameServer;
use bedrockcloud\cloudbridge\objects\VersionInfo;
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
     * @return GameServer|null
     */
    public function getCurrentServer(): ?GameServer
    {
        return CloudBridge::$gameServer[Server::getInstance()->getMotd()] ?? null;
    }

    /**
     * @param string $server
     * @return GameServer|null
     */
    public function getGameServer(string $server): ?GameServer
    {
        return CloudBridge::$gameServer[$server] ?? null;
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
     * @return GameServer[]
     */
    public function getGameServers(): array{
        return CloudBridge::$gameServer;
    }

    /**
     * @return CloudTemplate[]
     */
    public function getCloudTemplates(): array{
        return CloudBridge::$cloudTemplates;
    }
}