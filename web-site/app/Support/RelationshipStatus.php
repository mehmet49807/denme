<?php

namespace App\Support;

final class RelationshipStatus
{
    /** @return array<string, array{label: string, icon: string}> */
    public static function all(): array
    {
        return [
            'single' => ['label' => 'Bekar', 'icon' => '💚'],
            'relationship' => ['label' => 'İlişkide', 'icon' => '💞'],
            'engaged' => ['label' => 'Nişanlı', 'icon' => '💍'],
            'married' => ['label' => 'Evli', 'icon' => '💒'],
            'divorced' => ['label' => 'Boşanmış', 'icon' => '💔'],
            'widowed' => ['label' => 'Dul', 'icon' => '🤍'],
            'separated' => ['label' => 'Ayrı Yaşıyor', 'icon' => '🪴'],
            'complicated' => ['label' => 'Karışık', 'icon' => '🌀'],
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

    /** @return array{id: string, label: string, icon: string}|null */
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
        ];
    }
}
