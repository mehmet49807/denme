<?php

namespace App\Support;

class HobbyCatalog
{
    public static function max(): int
    {
        return (int) config('hobbies.max', 4);
    }

    /** @return list<array{id: string, label: string, icon: string, color: string}> */
    public static function all(): array
    {
        return config('hobbies.items', []);
    }

    /** @return list<string> */
    public static function ids(): array
    {
        return array_column(self::all(), 'id');
    }

    public static function find(string $id): ?array
    {
        foreach (self::all() as $item) {
            if ($item['id'] === $id) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>|null  $ids
     * @return list<array{id: string, label: string, icon: string, color: string}>
     */
    public static function resolve(?array $ids): array
    {
        if (! $ids) {
            return [];
        }

        $out = [];
        foreach ($ids as $id) {
            $item = self::find((string) $id);
            if ($item) {
                $out[] = $item;
            }
        }

        return $out;
    }

    /**
     * @return list<string>
     */
    public static function normalize(mixed $input): array
    {
        if (! is_array($input)) {
            return [];
        }

        $ids = array_values(array_unique(array_filter(array_map(
            static fn ($id) => is_string($id) ? trim($id) : '',
            $input
        ))));

        $allowed = self::ids();
        $ids = array_values(array_filter(
            $ids,
            static fn (string $id) => in_array($id, $allowed, true)
        ));

        return array_slice($ids, 0, self::max());
    }
}
