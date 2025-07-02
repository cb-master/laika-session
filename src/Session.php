<?php
/**
 * Library Name: Cloud Bill Master PHP Session Handler
 * Author: Showket Ahmed
 * Email: riyadhtayf@gmail.com 
 */

namespace CBM\Session;

class Session
{
    public static function set(string $key, mixed $value, string $for = 'APP'): void
    {
        SessionManager::start();
        $for = strtoupper($for);
        $_SESSION[$for][$key] = $value;
    }

    public static function get(string $key, string $for = 'APP'): mixed
    {
        SessionManager::start();
        $for = strtoupper($for);
        return $_SESSION[$for][$key] ?? null;
    }

    public static function has(string $key, string $for = 'APP'): bool
    {
        SessionManager::start();
        $for = strtoupper($for);
        return isset($_SESSION[$for][$key]);
    }

    public static function pop(string $key, string $for = 'APP'): void
    {
        $for = strtoupper($for);
        if(self::has($key, $for)) unset($_SESSION[$for][$key]);
    }

    public static function all(): array
    {
        SessionManager::start();
        return $_SESSION;
    }

    public static function regenerate(bool $delete = true): bool
    {
        SessionManager::start();
        return session_regenerate_id($delete);
    }

    public static function end(): void
    {
        SessionManager::end();
    }

    public static function id(): string
    {
        SessionManager::start();
        return session_id();
    }

    public static function name(): string
    {
        SessionManager::start();
        return session_name();
    }
}