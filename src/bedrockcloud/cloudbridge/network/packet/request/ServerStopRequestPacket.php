<?php

namespace bedrockcloud\cloudbridge\network\packet\request;

use bedrockcloud\cloudbridge\network\RequestPacket;

class ServerStopRequestPacket extends RequestPacket {
    const FAILURE_SERVER_EXISTENCE = 0;
}