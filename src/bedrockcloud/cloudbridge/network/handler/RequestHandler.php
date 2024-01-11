<?php

namespace bedrockcloud\cloudbridge\network\handler;

use bedrockcloud\cloudbridge\api\CloudAPI;
use pmmp\thread\ThreadSafeArray;
use bedrockcloud\cloudbridge\CloudBridge;
use pocketmine\Server;
use pocketmine\snooze\SleeperHandlerEntry;
use pocketmine\thread\Thread;

class RequestHandler extends Thread{
    public function __construct(private readonly SleeperHandlerEntry $handlerEntry, private ThreadSafeArray $buffer, private mixed $socket = null, private ?int $port = null, private bool $stop = false)
    {
        $this->port = (int)CloudAPI::getInstance()->getCloudPort();

        try {
            $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            $this->socket = $socket;

            if ($this->socket == null){
                Server::getInstance()->getLogger()->warning("§cCloudSocket is null.");
                Server::getInstance()->shutdown();
                return;
            }

            socket_bind($socket, '127.0.0.1', Server::getInstance()->getPort()+1);
            CloudBridge::getInstance()->getLogger()->info("UDP socket created");
        } catch (\Exception $e) {
            CloudBridge::getInstance()->getLogger()->critical("Failed to create Cloud socket. Stopping Server!");
            Server::getInstance()->shutdown();
        }
    }

    public function onRun(): void
    {
        $notifier = $this->handlerEntry->createNotifier();
        while (!$this->stop) {
            $data = null;
            $remote_ip = null;
            $remote_port = null;
            $bytes_received = socket_recvfrom($this->socket, $data, 65535, 0, $remote_ip, $remote_port);

            if ($bytes_received === false) {
                continue;
            }

            $this->buffer[] = $data;
            $notifier->wakeupSleeper();
        }
    }

    public function stop(): void
    {
        $this->stop = true;
        @socket_close($this->socket);
        CloudBridge::getInstance()->getLogger()->info("Cloud connection closed");
        Server::getInstance()->shutdown();
    }

    public function write(string $data): void {
        if ($this->stop) {
            return;
        }

        try {
            $message = $data . PHP_EOL;
            $bytes_sent = socket_sendto($this->socket, $message, strlen($message), 0, "127.0.0.1", (int)CloudAPI::getInstance()->getCloudPort());
            if ($bytes_sent === false) {}
        } catch (\Exception $exception) {
            $this->stop();
        }
    }
}