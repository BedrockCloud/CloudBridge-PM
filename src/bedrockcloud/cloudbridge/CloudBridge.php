<?php

namespace bedrockcloud\cloudbridge;

use bedrockcloud\cloudbridge\api\CloudAPI;
use bedrockcloud\cloudbridge\command\CloudCommand;
use bedrockcloud\cloudbridge\command\CloudNotifyCommand;
use bedrockcloud\cloudbridge\command\SaveCommand;
use bedrockcloud\cloudbridge\command\ServerInfoCommand;
use bedrockcloud\cloudbridge\listener\server\PlayerJoinListener;
use bedrockcloud\cloudbridge\listener\server\PlayerQuitListener;
use bedrockcloud\cloudbridge\network\DataPacket;
use bedrockcloud\cloudbridge\network\handler\PacketHandler;
use bedrockcloud\cloudbridge\network\handler\RequestHandler;
use bedrockcloud\cloudbridge\network\packet\CloudServerConnectPacket;
use bedrockcloud\cloudbridge\network\packet\CloudServerDisconnectPacket;
use bedrockcloud\cloudbridge\network\packet\request\CloudServerInfoRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\TemplateInfoRequestPacket;
use bedrockcloud\cloudbridge\network\packet\response\CloudServerInfoResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\TemplateInfoResponsePacket;
use bedrockcloud\cloudbridge\network\registry\PacketRegistry;
use bedrockcloud\cloudbridge\objects\CloudTemplate;
use bedrockcloud\cloudbridge\objects\CloudServer;
use bedrockcloud\cloudbridge\objects\CloudServerState;
use bedrockcloud\cloudbridge\objects\VersionInfo;
use bedrockcloud\cloudbridge\task\TimeoutTask;
use bedrockcloud\cloudbridge\utils\Utils;
use pmmp\thread\ThreadSafeArray;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use ReflectionException;

class CloudBridge extends PluginBase{
    use SingletonTrait{
        reset as private;
        setInstance as private;
    }

    private static RequestHandler $requestHandler;

    public static ?Config $config;

    public static array $requests = [];
    public array $queue = [];

    /** @var CloudServer[] */
    public static array $cloudServer = [];
    /** @var CloudTemplate[] */
    public static array $cloudTemplates = [];
    public static VersionInfo $versionInfo;

    private static ?CloudAPI $cloudAPI = null;
    public float|int $lastKeepALiveCheck = 0.0;

    public function onEnable(): void
    {
        self::setInstance($this);

        self::$cloudAPI = new CloudAPI();

        $buffer = new ThreadSafeArray();

        $sleeperEntry = Server::getInstance()->getTickSleeper()->addNotifier(function () use($buffer): void {
            while (($packet = $buffer->shift()) !== null) {
                PacketHandler::handleCloudPacket($packet);
            }
        });
        self::$requestHandler = new RequestHandler($sleeperEntry, $buffer);
        self::$requestHandler->start();

        self::$versionInfo = new VersionInfo("Cloud", "[]", "0.0.0", "NOT FOUND");

        try {
            PacketRegistry::registerPackets();
        } catch (ReflectionException $ignored) {}

        $this->getServer()->getPluginManager()->registerEvents(new PlayerJoinListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerQuitListener(), $this);

        $this->getServer()->getCommandMap()->registerAll("command:cloud", [
            new CloudCommand(),
            new SaveCommand(),
            new ServerInfoCommand(),
            new CloudNotifyCommand(),
        ]);

        $pk = new CloudServerConnectPacket();
        $pk->addValue("serverPort", $this->getServer()->getPort());
        $pk->addValue("serverPid", getmypid());
        $pk->sendPacket();

        $templateinfopacket = new TemplateInfoRequestPacket();
        $templateinfopacket->server = Server::getInstance()->getMotd();
        $templateinfopacket->submitRequest($templateinfopacket, function (DataPacket $dataPacket){
            if($dataPacket instanceof TemplateInfoResponsePacket) {
                $template = new CloudTemplate($dataPacket->getTemplateName(), $dataPacket->isMaintenance(), $dataPacket->isBeta(), $dataPacket->isLobby(), $dataPacket->getMaxPlayer(), $dataPacket->isStatic(), $dataPacket->getType());
                CloudBridge::$cloudTemplates[$dataPacket->getTemplateName()] = $template;

                $serverInfoPacket = new CloudServerInfoRequestPacket();
                $serverInfoPacket->server = Server::getInstance()->getMotd();
                $serverInfoPacket->submitRequest($serverInfoPacket, function (DataPacket $pk) use ($template, $dataPacket) {
                    if ($pk instanceof CloudServerInfoResponsePacket) {
                        $cloudServer = new CloudServer($pk->getServerInfoName(), $template);
                        $cloudServer->setState(CloudServerState::LOBBY, false);
                        $cloudServer->setPlayerCount($pk->getPlayerCount());
                        CloudBridge::$cloudServer[$pk->getServerInfoName()] = $cloudServer;
                    }
                });
            }
        });

        Utils::startTasks();

        $this->lastKeepALiveCheck = time();
        $this->getScheduler()->scheduleRepeatingTask(new TimeoutTask(), 20);
    }

    public function onDisable(): void{
        $pk = new CloudServerDisconnectPacket();
        $pk->serverName = Server::getInstance()->getMotd();
        $pk->sendPacket();

        self::$requestHandler->stop();
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

    public static function getPrefix(): string
    {
        return "§l§bCloud §r§8» §r";
    }

    /**
     * @return CloudAPI|null
     */
    public static function getCloudAPI(): ?CloudAPI
    {
        return self::$cloudAPI;
    }
}