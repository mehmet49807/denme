<?php

namespace App\Support;

/** @deprecated Use QuickMessages — geriye dönük uyumluluk */
final class GreetingTemplates
{
    /** @return list<string> */
    public static function all(): array
    {
        return QuickMessages::greetings();
    }
}
