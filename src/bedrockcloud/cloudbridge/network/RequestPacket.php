<?php

namespace bedrockcloud\cloudbridge\network;

use bedrockcloud\cloudbridge\CloudBridge;
use Closure;

class RequestPacket extends DataPacket
{

    private $requestId = "null";

    public function encode(): bool|string
    {
        $this->addValue("requestId", $this->requestId);
        return parent::encode();
    }

    public function submitRequest(DataPacket $packet, \Closure $closure): Closure
    {
        $this->requestId = uniqid();
        CloudBridge::getRequestHandler()->write($this->encode());
        CloudBridge::$requests[$this->requestId] = $closure;
        return $closure;
    }
}