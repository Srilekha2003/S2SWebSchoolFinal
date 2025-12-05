<?php

if (!function_exists('addTokenToBlacklist')) {
    /**
     * Add JWT token to blacklist
     */
    function addTokenToBlacklist(string $token): void
    {
        $file = WRITEPATH . 'blacklist.json';
        $blacklist = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
        $blacklist[$token] = time(); // store timestamp
        file_put_contents($file, json_encode($blacklist));
    }
}

if (!function_exists('isTokenBlacklisted')) {
    /**
     * Check if JWT token is blacklisted
     */
    function isTokenBlacklisted(string $token): bool
    {
        $file = WRITEPATH . 'blacklist.json';
        if (!file_exists($file)) return false;

        $blacklist = json_decode(file_get_contents($file), true) ?? [];
        return isset($blacklist[$token]);
    }
}
