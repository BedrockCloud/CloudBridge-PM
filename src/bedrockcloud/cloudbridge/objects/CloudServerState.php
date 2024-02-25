<?php

namespace bedrockcloud\cloudbridge\objects;

class CloudServerState
{

    public const NOT_REGISTERED = -1;
    public const LOBBY = 0;
    public const INGAME = 1;
    public const FULL = 2;

    public static function intToString(int $int): string{
        return match ($int) {
            self::LOBBY => "§aLobby",
            self::INGAME => "§cIngame",
            self::FULL => "§6Full",
            default => "§cNOT REGISTERED",
        };
    }

}