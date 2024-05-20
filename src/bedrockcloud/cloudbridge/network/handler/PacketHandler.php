<?php

namespace bedrockcloud\cloudbridge\network\handler;

use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\listener\cloud\CloudPacketReceiveEvent;
use bedrockcloud\cloudbridge\network\DataPacket;
use bedrockcloud\cloudbridge\network\RequestPacket;

class PacketHandler
{

    private static array $registeredPackets = [];

    /**
     * @param string $packetName
     * @return bool
     */
    public static function isRegistered(string $packetName): bool
    {
        return isset(self::$registeredPackets[$packetName]);
    }

    /**
     * @param string $packetName
     * @param string $packet
     */
    public static function registerPacket(string $packetName, string $packet): void
    {
        if (!self::isRegistered($packetName)) self::$registeredPackets[$packetName] = $packet;
    }

    /**
     * @param string $packetName
     * @return DataPacket|null
     */
    public static function getPacketClassByName(string $packetName): ?DataPacket{
        if (self::isRegistered($packetName)) return new self::$registeredPackets[$packetName];

        return null;
    }

    /**
     * @param string $packetName
     */
    public static function unregisterPacket(string $packetName): void{
        unset(self::$registeredPackets[$packetName]);
    }

    public static function handleCloudPacket(string $packetBuffer): void
    {
        $data = json_decode($packetBuffer, true);
        if (empty($data["packetName"]) || !self::isRegistered($data["packetName"]) || is_null($data)) return;
        $packet = self::getPacketClassByName($data["packetName"]);
        if ($packet instanceof DataPacket) {
            $packet->data = $data;

            $packet->handle();
            if ($packet instanceof RequestPacket) {
                if (isset($packet->data["requestId"]) && isset($packet->data["type"])) {
                    if ($packet->data["type"] == DataPacket::TYPE_RESPONSE) {
                        $closure = CloudBridge::$requests[$packet->data["requestId"]] ?? null;
                        if ($closure !== null) ($closure)($packet);
                        unset(CloudBridge::$requests[$packet->data["requestId"]]);
                    }
                }
            }
            $event = new CloudPacketReceiveEvent($packet);
            $event->call();
        }
    }
}