<?php

namespace bedrockcloud\cloudbridge\objects;

class CloudServerState
{

    public const NOT_REGISTERED = -1;
    public const LOBBY = 0;
    public const INGAME = 1;
    public const FULL = 2;

    public static function intToString(int $int): string{
        if ($int === self::NOT_REGISTERED){
            return "§cNOT REGISTERED";
        } elseif($int === self::LOBBY){
            return "§aLobby";
        } elseif($int === self::INGAME){
            return "§cIngame";
        } elseif($int === self::FULL){
            return "§6Full";
        } else {
            return "§cNOT REGISTERED";
        }
    }

}