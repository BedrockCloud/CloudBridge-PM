<?php

namespace bedrockcloud\cloudbridge;

use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\scheduler\Task;
use pocketmine\snooze\SleeperNotifier;
use ReflectionException;
use bedrockcloud\cloudbridge\command\CloudCommand;
use bedrockcloud\cloudbridge\command\CloudNotifyCommand;
use bedrockcloud\cloudbridge\command\PermissionCommand;
use bedrockcloud\cloudbridge\command\SaveCommand;
use bedrockcloud\cloudbridge\command\ServerInfoCommand;
use bedrockcloud\cloudbridge\crashLoggerSystem\utils\CrashDumpReader;
use bedrockcloud\cloudbridge\crashLoggerSystem\utils\DiscordHandler;
use bedrockcloud\cloudbridge\listener\server\PlayerJoinListener;
use bedrockcloud\cloudbridge\listener\server\PlayerQuitListener;
use bedrockcloud\cloudbridge\network\DataPacket;
use bedrockcloud\cloudbridge\network\handler\PacketHandler;
use bedrockcloud\cloudbridge\network\handler\RequestHandler;
use bedrockcloud\cloudbridge\network\packet\CloudPlayerAddPermissionPacket;
use bedrockcloud\cloudbridge\network\packet\GameServerConnectPacket;
use bedrockcloud\cloudbridge\network\packet\GameServerDisconnectPacket;
use bedrockcloud\cloudbridge\network\packet\GameServerInfoRequestPacket;
use bedrockcloud\cloudbridge\network\packet\GameServerInfoResponsePacket;
use bedrockcloud\cloudbridge\network\packet\KeepALivePacket;
use bedrockcloud\cloudbridge\network\packet\ListCloudPlayersRequestPacket;
use bedrockcloud\cloudbridge\network\packet\ListCloudPlayersResponsePacket;
use bedrockcloud\cloudbridge\network\packet\ListServerRequestPacket;
use bedrockcloud\cloudbridge\network\packet\ListServerResponsePacket;
use bedrockcloud\cloudbridge\network\packet\ListTemplatesRequestPacket;
use bedrockcloud\cloudbridge\network\packet\ListTemplatesResponsePacket;
use bedrockcloud\cloudbridge\network\packet\PlayerKickPacket;
use bedrockcloud\cloudbridge\network\packet\PlayerMovePacket;
use bedrockcloud\cloudbridge\network\packet\PlayerMessagePacket;
use bedrockcloud\cloudbridge\network\packet\ProxyPlayerJoinPacket;
use bedrockcloud\cloudbridge\network\packet\ProxyPlayerQuitPacket;
use bedrockcloud\cloudbridge\network\packet\SendToHubPacket;
use bedrockcloud\cloudbridge\network\packet\StartGroupPacket;
use bedrockcloud\cloudbridge\network\packet\StartServerPacket;
use bedrockcloud\cloudbridge\network\packet\StopGroupPacket;
use bedrockcloud\cloudbridge\network\packet\StopServerPacket;
use bedrockcloud\cloudbridge\network\packet\VersionInfoPacket;
use bedrockcloud\cloudbridge\network\task\RequestTask;
use bedrockcloud\cloudbridge\objects\CloudGroup;
use bedrockcloud\cloudbridge\objects\GameServer;
use bedrockcloud\cloudbridge\objects\GameServerState;
use bedrockcloud\cloudbridge\objects\VersionInfo;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;

use ThreadedLogger;

class CloudBridge extends PluginBase
{

    public static array $requests = [];

    private static RequestHandler $requestHandler;

    public static ?Config $config;

    private static CloudBridge $instance;
    public static array $qeueuPlayer = [];

    /** @var GameServer[] */
    public static array $gameServer = [];
    public static VersionInfo $versionInfo;

    public array $queue = [];

    public function onLoad(): void
    {
        self::$instance = $this;
    }

    public function onEnable(): void
    {
        $buffer = new \Threaded();
        $socketNotifier = new SleeperNotifier();
        self::$requestHandler = new RequestHandler($socketNotifier, $buffer);

        $this->getServer()->getTickSleeper()->addNotifier($socketNotifier, function () use($buffer): void {
            while (($packet = $buffer->shift()) !== null) {
                PacketHandler::handleCloudPacket($packet);
            }
        });

        self::$versionInfo = new VersionInfo("Cloud", "[]", "0.0.0", "NOT FOUND");

        $this->checkOldCrashDumps();
        self::registerPackets();
        $this->getServer()->getPluginManager()->registerEvents(new PlayerJoinListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerQuitListener(), $this);
        $this->getServer()->getCommandMap()->register("command:bedrockcloud", new CloudCommand());
        $this->getServer()->getCommandMap()->register("command:bedrockcloud", new SaveCommand());
        $this->getServer()->getCommandMap()->register("command:bedrockcloud", new ServerInfoCommand());
        $this->getServer()->getCommandMap()->register("command:bedrockcloud", new CloudNotifyCommand());
        $pk = new GameServerConnectPacket();
        $pk->addValue("serverPort", $this->getServer()->getPort());
        $pk->addValue("serverPid", getmypid());
        $pk->sendPacket();
        $serverInfoPacket = new GameServerInfoRequestPacket();
        $serverInfoPacket->server = Server::getInstance()->getMotd();
        $serverInfoPacket->submitRequest($serverInfoPacket, function (DataPacket $dataPacket) {
            if($dataPacket instanceof GameServerInfoResponsePacket) {
                $gameServer = new GameServer($dataPacket->getServerInfoName(), new CloudGroup($dataPacket->getTemplateName(), $dataPacket->isMaintenance(), $dataPacket->isBeta(), $dataPacket->isLobby(), $dataPacket->getMaxPlayer(), $dataPacket->getState()));
                $gameServer->setState(GameServerState::LOBBY, false);
                $gameServer->setIsPrivate($dataPacket->isPrivate());
                $gameServer->setPlayerCount($dataPacket->getPlayerCount());
                self::$gameServer[$dataPacket->getServerInfoName()] = $gameServer;
            }
        });
        $this->getScheduler()->scheduleRepeatingTask(new class extends Task{
            public function onRun(): void
            {
                $listServers = new ListServerRequestPacket();
                $listServers->submitRequest($listServers, function (DataPacket $dP) {
                    if ($dP instanceof ListServerResponsePacket) {
                        $servers = json_decode($dP->data["servers"], true);
                        foreach ($servers as $name) {
                            $serverInfoPacket = new GameServerInfoRequestPacket();
                            $serverInfoPacket->server = $name;
                            $serverInfoPacket->submitRequest($serverInfoPacket, function (DataPacket $dataPacket) use ($name, $servers) {
                                if ($dataPacket instanceof GameServerInfoResponsePacket) {
                                    $gameServer = new GameServer($dataPacket->getServerInfoName(), new CloudGroup($dataPacket->getTemplateName(), $dataPacket->isMaintenance(), $dataPacket->isBeta(), $dataPacket->isLobby(), $dataPacket->getMaxPlayer(), $dataPacket->getState()));
                                    $gameServer->setIsPrivate($dataPacket->isPrivate());
                                    $gameServer->setPlayerCount($dataPacket->getPlayerCount());
                                    $gameServer->setServerState($dataPacket->getState());
                                    if (!isset(CloudBridge::$gameServer[$name])) {
                                        CloudBridge::$gameServer[$name] = $gameServer;
                                    } elseif (isset(CloudBridge::$gameServer[$name])) {
                                        $gs = CloudBridge::$gameServer[$name];
                                        $gs->setIsPrivate($dataPacket->isPrivate());
                                        $gs->setPlayerCount($dataPacket->getPlayerCount());
                                        $gs->setServerState($dataPacket->getState());
                                    }
                                }

                                foreach (CloudBridge::$gameServer as $gameServer) {
                                    if (!in_array($gameServer->getName(), $servers)) {
                                        unset(CloudBridge::$gameServer[$gameServer->getName()]);
                                    }
                                }
                            });
                        }
                    }
                });
            }
        }, 20);
    }

    public function onDisable(): void
    {
        $pk = new GameServerDisconnectPacket();
        $pk->serverName = self::getGameServer()->getName();
        $pk->sendPacket();
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $player->kick("Server was closed");
        }
        $this->checkNewCrashDump();
        self::$requestHandler->stop(true);
    }

    /**
     * @return GameServer
     */
    public static function getGameServer(): GameServer
    {
        return self::$gameServer[Server::getInstance()->getMotd()];
    }

    /**
     * @return VersionInfo
     */
    public static function getVersionInfo(): VersionInfo
    {
        return self::$versionInfo;
    }


    /**
     * @throws ReflectionException
     */
    private static function registerPackets()
    {
        $packets = [
            GameServerConnectPacket::class,
            GameServerDisconnectPacket::class,
            GameServerInfoRequestPacket::class,
            GameServerInfoResponsePacket::class,
            ListServerRequestPacket::class,
            ListServerResponsePacket::class,
            ProxyPlayerJoinPacket::class,
            ProxyPlayerQuitPacket::class,
            KeepALivePacket::class,
            StartGroupPacket::class,
            StartServerPacket::class,
            StopGroupPacket::class,
            StopServerPacket::class,
            CloudPlayerAddPermissionPacket::class,
            VersionInfoPacket::class,
            PlayerMovePacket::class,
            ListCloudPlayersRequestPacket::class,
            ListCloudPlayersResponsePacket::class,
            PlayerMessagePacket::class,
			PlayerKickPacket::class,
            SendToHubPacket::class,
            ListTemplatesRequestPacket::class,
            ListTemplatesResponsePacket::class,
        ];

        foreach ($packets as $packet) {
            $reflection = new \ReflectionClass($packet);
            PacketHandler::registerPacket($reflection->getShortName(), $packet);
        }
    }

    /**
     * @return RequestHandler
     */
    public static function getRequestHandler(): RequestHandler
    {
        return self::$requestHandler;
    }

    public function getConfig(): Config
    {
        return parent::getConfig();
    }

    /**
     * @return CloudBridge
     */
    public static function getInstance(): CloudBridge
    {
        return self::$instance;
    }

    public static function getPrefix(): string
    {
        return "§l§bCloud §r§8» §r";
    }


    public function getTemplate(): string {
        return $this->getServerProperties()->get("template");
    }

    public function getCloudPort(): int {
        return $this->getServerProperties()->get("cloud-port");
    }

    public function getCloudPassword(): string {
        return $this->getServerProperties()->get("cloud-password");
    }

    public function getCloudPath(): string {
        return $this->getServerProperties()->get("cloud-path");
    }

    public function getServerProperties(): Config {
        return new Config(Server::getInstance()->getDataPath() . "server.properties", 0);
    }


    public function checkOldCrashDumps(): void{
        $validityDuration = 24 * 60 * 60;
        $delete = false;

        $files = $this->getCrashdumpFiles();
        $this->getLogger()->info("Checking old crash dumps (files: ".count($files).")");

        $removed = 0;
        foreach($files as $filePath){
            try{
                $crashDumpReader = new CrashDumpReader($filePath);

                if(!$crashDumpReader->hasRead()){
                    continue;
                }

                if($delete === true and time() - $crashDumpReader->getCreationTime() >= $validityDuration){
                    unlink($filePath);
                    ++$removed;
                }
            }catch(\Throwable $e){
                $this->getLogger()->warning("Error during file check of \"".basename($filePath)."\": ".$e->getMessage()." in file ".$e->getFile()." on line ".$e->getLine());
                foreach(explode("\n", $e->getTraceAsString()) as $traceString){
                    $this->getLogger()->debug("[ERROR] ".$traceString);
                }
            }
        }

        $fileAmount = count($files);
        $percentage = $fileAmount > 0 ? round($removed * 100 / $fileAmount, 2) : "NAN";

        $message = "Checks finished, Deleted crash dump files: ".$removed." (".$percentage."%)";
        if($removed > 0){
            $this->getLogger()->notice($message);
        }else{
            $this->getLogger()->info($message);
        }
    }

    public function checkNewCrashDump(): void{
        $this->getLogger()->debug("Checking for new crash dump");
        $files = $this->getCrashdumpFiles();

        $startTime = (int) $this->getServer()->getStartTime();
        foreach($files as $filePath){
            try{
                $crashDumpReader = new CrashDumpReader($filePath);

                if(!$crashDumpReader->hasRead() or $crashDumpReader->getCreationTime() < $startTime){
                    continue;
                }

                $this->getLogger()->notice("New crash dump found. Sending now.");
                $this->reportCrashDump($crashDumpReader);
            }catch(\Throwable $e){
                $this->getLogger()->warning("Error while checking potentially new crash dump \"".basename($filePath)."\": ".$e->getMessage()." in file ".$e->getFile()." on line ".$e->getLine());
                foreach(explode("\n", $e->getTraceAsString()) as $traceString){
                    $this->getLogger()->debug("[ERROR] ".$traceString);
                }
            }
        }

        $this->getLogger()->debug("Checks finished");

    }

    public function reportCrashDump(CrashDumpReader $crashDumpReader): void{
        if($crashDumpReader->hasRead()){
            $handler = new DiscordHandler("https://discord.com/api/webhooks/978372184831578112/I1P47OdhG72tBihIibuaCjZDxXBM0McD3eDD05uYOIYaj78JPGw7rd1yC4A6h4Ov699x", $crashDumpReader);
            $handler->announceCrash = true;
            $handler->fullPath = true;
            $handler->dateFormat = "d.m.Y (l): H:i:s [e]";

            $handler->submit();
            $this->getLogger()->debug("Crash dump sent");
        }
    }

    public function getCrashdumpFiles(): array{
        return glob($this->getServer()->getDataPath()."crashdumps/*.log");
    }


}