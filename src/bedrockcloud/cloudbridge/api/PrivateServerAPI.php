<?php

namespace bedrockcloud\cloudbridge\api;

use bedrockcloud\cloudbridge\CloudBridge;

class PrivateServerAPI {

    public static function getIsPrivate(): string {
        return CloudAPI::getInstance()->getServerProperties()->get("is-private");
    }

    public static function getServerOwner(): ?string {
        return CloudAPI::getInstance()->getServerProperties()->exists("pserver-owner") ? CloudAPI::getInstance()->getServerProperties()->get("pserver-owner") : null;
    }
}