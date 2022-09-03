<?php

namespace bedrockcloud\cloudbridge\network\handler;

use pocketmine\scheduler\ClosureTask;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\thread\Thread;
use bedrockcloud\cloudbridge\CloudBridge;
use pocketmine\Server;

class RequestHandler extends Thread{

    private $socket;
    private bool $stop;
    private SleeperNotifier $sleeperNotifier;
    private \Threaded $buffer;

    public function __construct(SleeperNotifier $sleeperNotifier, \Threaded $buffer)
    {
        $this->stop = false;
        $this->sleeperNotifier = $sleeperNotifier;
        $this->buffer = $buffer;

        set_time_limit(0);

        try {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            $this->socket = $socket;
            socket_connect($socket, "127.0.0.1", (int)CloudBridge::getInstance()->getCloudPort());
            socket_set_option($socket, SOL_TCP, TCP_NODELAY, 1);
            CloudBridge::getInstance()->getLogger()->info("§bCloud §aConnection opened to 127.0.0.1:" . CloudBridge::getInstance()->getCloudPort());
            $this->start(PTHREADS_INHERIT_NONE);
        }catch (\Exception $e) {
            CloudBridge::getInstance()->getLogger()->critical("Connection to Cloud interrupted");
        }
    }

    protected function onRun(): void
    {
        $this->registerClassLoaders();
        while (!$this->stop) {
            @ob_start();
            try {
                $request = @socket_read($this->socket, 2048, PHP_NORMAL_READ);
            } catch (\Exception $ignored) {
                break;
            }

            if(!$request) break;

            $this->buffer[] = $request;
            $this->sleeperNotifier->wakeupSleeper();

            @ob_end_flush();
        }
    }

    /**
     * @return bool
     */
    public function isStop(): bool
    {
        return $this->stop;
    }

    public function stop()
    {
        $this->stop = true;
        socket_close($this->socket);
        CloudBridge::getInstance()->getLogger()->info("§bCloud §4Connection closed");
        Server::getInstance()->shutdown();
    }

    public function write(string $data)
    {
        if($this->stop) return;
        @socket_sendto($this->socket, $data.PHP_EOL, 65535, 0, "127.0.0.1", (int)CloudBridge::getInstance()->getCloudPort());
    }
}