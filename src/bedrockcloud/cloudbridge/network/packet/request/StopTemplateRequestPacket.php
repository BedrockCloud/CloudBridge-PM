<?php

namespace bedrockcloud\cloudbridge\network\packet\request;

use bedrockcloud\cloudbridge\network\RequestPacket;

class StopTemplateRequestPacket extends RequestPacket {
    const FAILURE_TEMPLATE_EXISTENCE = 0;
    const FAILURE_TEMPLATE_NOT_RUNNING = 1;
}