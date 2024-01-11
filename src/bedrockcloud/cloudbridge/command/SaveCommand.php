<?php


namespace bedrockcloud\cloudbridge\command;

use bedrockcloud\cloudbridge\api\CloudAPI;
use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\network\DataPacket;
use bedrockcloud\cloudbridge\network\packet\request\SaveServerRequestPacket;
use bedrockcloud\cloudbridge\network\packet\response\SaveServerResponsePacket;
use bedrockcloud\cloudbridge\objects\CloudTemplate;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class SaveCommand extends Command
{

    public function __construct()
    {
        parent::__construct("save", "Save CloudServer");
        $this->setPermission("cloud.admin");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $template = CloudAPI::getInstance()->getCurrentTemplate();

        if (!$template instanceof CloudTemplate){
            $sender->sendMessage(CloudBridge::getPrefix() . "§r§f§cError whilst saving files§7!§c Template don't exists§7!");
            return;
        }

        $serverName = CloudBridge::getInstance()->getServer()->getMotd();

        if ($sender->hasPermission("cloud.admin")){
			Server::getInstance()->getCommandMap()->dispatch($sender, "save-all");

            $pk = new SaveServerRequestPacket();
            $pk->addValue("serverName", $serverName);

            $pk->submitRequest($pk, function (DataPacket $dataPacket) use ($pk, $sender, $serverName) {
               if ($dataPacket instanceof SaveServerResponsePacket) {
                   if ($dataPacket->isSuccess()) {
                       $sender->sendMessage(CloudBridge::getPrefix() . "§aYou have saved the server §e{$serverName} §asuccessfully§7.");
                   } else {
                       if ($dataPacket->getFailureId() === $pk::FAILURE_SERVER_EXISTENCE) {
                           $sender->sendMessage(CloudBridge::getPrefix() . "§cThis server don't exists.");
                       }
                   }
               }
            });
		}
    }
}