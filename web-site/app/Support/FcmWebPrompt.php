<?php

namespace App\Support;

final class FcmWebPrompt
{
    public const SESSION_KEY = 'fcm_web_prompt';

    public static function arm(): void
    {
        session([self::SESSION_KEY => true]);
    }

    public static function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public static function pending(): bool
    {
        return (bool) session(self::SESSION_KEY);
    }
}
