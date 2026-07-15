<?php

namespace App\Support;

final class RelationshipStatus
{
    /** @return array<string, array{label: string, icon: string, color: string}> */
    public static function all(): array
    {
        return [
            'single' => ['label' => 'Bekar', 'icon' => '💚', 'color' => 'emerald'],
            'relationship' => ['label' => 'İlişkide', 'icon' => '💞', 'color' => 'rose'],
            'engaged' => ['label' => 'Nişanlı', 'icon' => '💍', 'color' => 'amber'],
            'married' => ['label' => 'Evli', 'icon' => '💒', 'color' => 'violet'],
            'divorced' => ['label' => 'Boşanmış', 'icon' => '💔', 'color' => 'slate'],
            'widowed' => ['label' => 'Dul', 'icon' => '🤍', 'color' => 'sky'],
            'separated' => ['label' => 'Ayrı Yaşıyor', 'icon' => '🪴', 'color' => 'teal'],
            'complicated' => ['label' => 'Karışık', 'icon' => '🌀', 'color' => 'orange'],
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function isValid(?string $status): bool
    {
        return $status !== null && $status !== '' && isset(self::all()[$status]);
    }

    public static function label(?string $status): ?string
    {
        if (! self::isValid($status)) {
            return null;
        }

        return self::all()[$status]['label'];
    }

    public static function icon(?string $status): ?string
    {
        if (! self::isValid($status)) {
            return null;
        }

        return self::all()[$status]['icon'];
    }

    public static function color(?string $status): ?string
    {
        if (! self::isValid($status)) {
            return null;
        }

        return self::all()[$status]['color'];
    }

    /** @return array{id: string, label: string, icon: string, color: string}|null */
    public static function resolve(?string $status): ?array
    {
        if (! self::isValid($status)) {
            return null;
        }

        $item = self::all()[$status];

        return [
            'id' => $status,
            'label' => $item['label'],
            'icon' => $item['icon'],
            'color' => $item['color'],
        ];
    }
}
