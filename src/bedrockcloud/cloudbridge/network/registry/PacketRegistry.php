<?php

namespace bedrockcloud\cloudbridge\network\registry;

use bedrockcloud\cloudbridge\network\handler\PacketHandler;
use bedrockcloud\cloudbridge\network\packet\CloudServerConnectPacket;
use bedrockcloud\cloudbridge\network\packet\CloudServerDisconnectPacket;
use bedrockcloud\cloudbridge\network\packet\KeepALivePacket;
use bedrockcloud\cloudbridge\network\packet\PlayerKickPacket;
use bedrockcloud\cloudbridge\network\packet\PlayerMessagePacket;
use bedrockcloud\cloudbridge\network\packet\PlayerMovePacket;
use bedrockcloud\cloudbridge\network\packet\proxy\ProxyPlayerJoinPacket;
use bedrockcloud\cloudbridge\network\packet\proxy\ProxyPlayerQuitPacket;
use bedrockcloud\cloudbridge\network\packet\request\CheckPlayerMaintenanceRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\CloudPlayerInfoRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\CloudServerInfoRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\ListCloudPlayersRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\ListServerRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\ListTemplatesRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\SaveServerRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\ServerStartRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\ServerStopRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\TemplateInfoRequestPacket;
use bedrockcloud\cloudbridge\network\packet\response\CheckPlayerMaintenanceResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\CloudPlayerInfoResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\CloudServerInfoResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\ListCloudPlayersResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\ListServerResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\ListTemplatesResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\SaveServerResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\ServerStartResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\ServerStopResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\TemplateInfoResponsePacket;
use bedrockcloud\cloudbridge\network\packet\SendToHubPacket;
use bedrockcloud\cloudbridge\network\packet\TemplateUpdatePacket;
use bedrockcloud\cloudbridge\network\packet\VersionInfoPacket;

class PacketRegistry {
    /**
     * @throws \ReflectionException
     */
    public static function registerPackets(): void{
        $packets = [
            CloudServerConnectPacket::class,
            CloudServerDisconnectPacket::class,
            CloudServerInfoRequestPacket::class,
            CloudServerInfoResponsePacket::class,
            ListServerRequestPacket::class,
            ListServerResponsePacket::class,
            ProxyPlayerJoinPacket::class,
            ProxyPlayerQuitPacket::class,
            KeepALivePacket::class,
            VersionInfoPacket::class,
            PlayerMovePacket::class,
            ListCloudPlayersRequestPacket::class,
            ListCloudPlayersResponsePacket::class,
            PlayerMessagePacket::class,
            PlayerKickPacket::class,
            SendToHubPacket::class,
            ListTemplatesRequestPacket::class,
            ListTemplatesResponsePacket::class,
            TemplateInfoRequestPacket::class,
            TemplateInfoResponsePacket::class,
            CloudPlayerInfoRequestPacket::class,
            CloudPlayerInfoResponsePacket::class,
            ServerStartRequestPacket::class,
            ServerStopRequestPacket::class,
            ServerStartResponsePacket::class,
            ServerStopResponsePacket::class,
            SaveServerRequestPacket::class,
            SaveServerResponsePacket::class,
            CheckPlayerMaintenanceRequestPacket::class,
            CheckPlayerMaintenanceResponsePacket::class,
            TemplateUpdatePacket::class,
        ];

        foreach ($packets as $packet) {
            $reflection = new \ReflectionClass($packet);
            PacketHandler::registerPacket($reflection->getShortName(), $packet);
        }
    }
}