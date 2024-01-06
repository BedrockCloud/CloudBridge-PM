<?php

namespace bedrockcloud\cloudbridge\network;

use bedrockcloud\cloudbridge\api\PrivateServerAPI;
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
        $this->addValue("isPrivate", '"'.PrivateServerAPI::getIsPrivate().'"');
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
    public function addValue(string $key, string $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param string $key
     * @param int $value
     */
    public function addIntValue(string $key, int $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @return false|string
     */
    public function encode() {
        $this->addValue("packetName", $this->getPacketName());
        return json_encode($this->data);
    }

    /**
     * @param string $data
     * @return array
     */
    public function decode(string $data) {
        return json_decode($data);
    }

    /**
     * @return string
     */
    public function getServerName(): string
    {
        return CloudBridge::getInstance()->getServer()->getMotd();
    }

    public function sendPacket()
    {
        CloudBridge::getRequestHandler()->write($this->encode());
    }

    public function handle(){}

}