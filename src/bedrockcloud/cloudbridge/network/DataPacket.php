<?php

namespace bedrockcloud\cloudbridge\network;

use bedrockcloud\cloudbridge\CloudBridge;

class DataPacket
{

    const TYPE_REQUEST = 0;
    const TYPE_RESPONSE = 1;

    /** @var array */
    public $data = [];

    /**
     * CloudPacket constructor.
     */
    public function __construct()
    {
        $this->addValue("serverName", $this->getServerName());
    }

    /**
     * @return string
     */
    public function getPacketName() : string{
        return "DataPacket (BedrockCloud-PMMP)";
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function addValue(string $key, string $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * @param string $key
     * @param int $value
     */
    public function addIntValue(string $key, int $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * @param string $key
     * @param array $value
     */
    public function addArrayValue(string $key, array $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * @return false|string
     */
    public function encode(): bool|string
    {
        $reflection = new \ReflectionClass($this);
        $this->addValue("packetName", $reflection->getShortName());
        return json_encode($this->data);
    }

    /**
     * @param string $data
     * @return array
     */
    public function decode(string $data): array
    {
        return json_decode($data);
    }

    /**
     * @return string
     */
    public function getServerName(): string
    {
        return CloudBridge::getInstance()->getServer()->getMotd();
    }

    public function sendPacket(): void
    {
        CloudBridge::getRequestHandler()->write($this->encode());
    }

    public function handle(): void{}

}