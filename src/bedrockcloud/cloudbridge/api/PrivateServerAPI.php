<?php

namespace bedrockcloud\cloudbridge\api;

use bedrockcloud\cloudbridge\CloudBridge;

class PrivateServerAPI {

    public static function getIsPrivate(): string {
        return CloudBridge::getInstance()->getServerProperties()->get("is-private");
    }

    public static function getServerOwner(): ?string {
        return CloudBridge::getInstance()->getServerProperties()->exists("pserver-owner") ? CloudBridge::getInstance()->getServerProperties()->get("pserver-owner") : null;
    }
}