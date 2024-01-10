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
use bedrockcloud\cloudbridge\network\packet\GameServerConnectPacket;
use bedrockcloud\cloudbridge\network\packet\GameServerDisconnectPacket;
use bedrockcloud\cloudbridge\network\packet\request\GameServerInfoRequestPacket;
use bedrockcloud\cloudbridge\network\packet\request\TemplateInfoRequestPacket;
use bedrockcloud\cloudbridge\network\packet\response\GameServerInfoResponsePacket;
use bedrockcloud\cloudbridge\network\packet\response\TemplateInfoResponsePacket;
use bedrockcloud\cloudbridge\network\registry\PacketRegistry;
use bedrockcloud\cloudbridge\objects\CloudGroup;
use bedrockcloud\cloudbridge\objects\CloudTemplate;
use bedrockcloud\cloudbridge\objects\GameServer;
use bedrockcloud\cloudbridge\objects\GameServerState;
use bedrockcloud\cloudbridge\objects\VersionInfo;
use bedrockcloud\cloudbridge\utils\Utils;
use pmmp\thread\ThreadSafeArray;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskHandler;
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

    /** @var GameServer[] */
    public static array $gameServer = [];
    /** @var CloudTemplate[] */
    public static array $cloudTemplates = [];
    public static VersionInfo $versionInfo;

    private static ?CloudAPI $cloudAPI = null;

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

        $this->getServer()->getCommandMap()->registerAll("command:bedrockcloud", [
            new CloudCommand(),
            new SaveCommand(),
            new ServerInfoCommand(),
            new CloudNotifyCommand(),
        ]);

        $pk = new GameServerConnectPacket();
        $pk->addValue("serverPort", $this->getServer()->getPort());
        $pk->addValue("serverPid", getmypid());
        $pk->sendPacket();
        $serverInfoPacket = new GameServerInfoRequestPacket();
        $serverInfoPacket->server = Server::getInstance()->getMotd();
        $serverInfoPacket->submitRequest($serverInfoPacket, function (DataPacket $dataPacket) {
            if($dataPacket instanceof GameServerInfoResponsePacket) {
                $gameServer = new GameServer($dataPacket->getServerInfoName(), new CloudGroup($dataPacket->getTemplateName(), $dataPacket->isMaintenance(), $dataPacket->isBeta(), $dataPacket->isLobby(), $dataPacket->getMaxPlayer(), $dataPacket->getState(), $dataPacket->isStatic()));
                $gameServer->setState(GameServerState::LOBBY, false);
                $gameServer->setPlayerCount($dataPacket->getPlayerCount());
                self::$gameServer[$dataPacket->getServerInfoName()] = $gameServer;
            }
        });

        $templateinfopacket = new TemplateInfoRequestPacket();
        $templateinfopacket->server = Server::getInstance()->getMotd();
        $templateinfopacket->submitRequest($templateinfopacket, function (DataPacket $dataPacket){
            if($dataPacket instanceof TemplateInfoResponsePacket) {
                $template = new CloudTemplate($dataPacket->getTemplateName(), $dataPacket->isMaintenance(), $dataPacket->isBeta(), $dataPacket->isLobby(), $dataPacket->getMaxPlayer());
                $template->setIsPrivate($dataPacket->isPrivate());
                self::$cloudTemplates[$dataPacket->getTemplateName()] = $template;
            }
        });

        Utils::startTasks();
    }

    public function onDisable(): void{
        $pk = new GameServerDisconnectPacket();
        $pk->serverName = CloudAPI::getInstance()->getCurrentServer()->getName();
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