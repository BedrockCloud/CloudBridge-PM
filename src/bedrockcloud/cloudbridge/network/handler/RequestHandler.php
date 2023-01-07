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

    public function __construct(SleeperNotifier $sleeperNotifier, \Threaded $buffer)
    {
        $this->sleeperNotifier = $sleeperNotifier;
        $this->buffer = $buffer;

        try {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            $this->socket = $socket;
            socket_connect($socket, "127.0.0.1", (int)CloudBridge::getInstance()->getCloudPort());
            CloudBridge::getInstance()->getLogger()->info("Cloud Connection opened to 127.0.0.1:" . CloudBridge::getInstance()->getCloudPort());
            $this->start();
        } catch (\Exception $e) {
            CloudBridge::getInstance()->getLogger()->critical("Connection to Cloud interrupted");
        }
    }

    public function run(): void
    {
        while (!$this->stop) {
            try {
                $request = @socket_read($this->socket, 1024, PHP_NORMAL_READ);
            } catch (\Exception $ignored) {
                return;
            }

            if (!$request) {
                return;
            }

            $this->buffer[] = $request;
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
            socket_write($this->socket, $data . PHP_EOL);
        } catch (\Exception $exception) {
            Server::getInstance()->shutdown();
        }
    }
}