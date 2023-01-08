<?php

namespace bedrockcloud\cloudbridge\network\handler;

use pocketmine\scheduler\ClosureTask;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\thread\Thread;
use bedrockcloud\cloudbridge\CloudBridge;
use pocketmine\Server;

class RequestHandler extends \Thread
{

    private $socket;
    private bool $stop = false;
    private SleeperNotifier $sleeperNotifier;
    private \Threaded $buffer;
    protected $port;

    public function __construct(SleeperNotifier $sleeperNotifier, \Threaded $buffer)
    {
        $this->sleeperNotifier = $sleeperNotifier;
        $this->buffer = $buffer;
        $this->port = (int)CloudBridge::getInstance()->getCloudPort();

        try {
            $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            $this->socket = $socket;
            socket_bind($socket, '127.0.0.1', Server::getInstance()->getPort()+1);
            CloudBridge::getInstance()->getLogger()->info("UDP socket created");
            $this->start();
        } catch (\Exception $e) {
            CloudBridge::getInstance()->getLogger()->critical("Failed to create UDP socket");
        }
    }

    public function run(): void
    {
        while (!$this->stop) {
            $data = null;
            $remote_ip = null;
            $remote_port = null;
            $bytes_received = socket_recvfrom($this->socket, $data, 65535, 0, $remote_ip, $remote_port);

            if ($bytes_received === false) {
                continue;
            }

            $this->buffer[] = $data;
            $this->sleeperNotifier->wakeupSleeper();
        }
    }

    public function stop(): void
    {
        $this->stop = true;
        socket_close($this->socket);
        CloudBridge::getInstance()->getLogger()->info("Cloud Connection closed");
        Server::getInstance()->shutdown();
    }

    public function write(string $data): void
    {
        if ($this->stop) {
            return;
        }

        try {
            $message = $data . PHP_EOL;
            $bytes_sent = socket_sendto($this->socket, $message, strlen($message), 0, "127.0.0.1", CloudBridge::getInstance()->getCloudPort());
            if ($bytes_sent === false) {}
        } catch (\Exception $exception) {
            Server::getInstance()->getLogger()->logException($exception);
        }
    }
}