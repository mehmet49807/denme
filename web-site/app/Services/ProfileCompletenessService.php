<?php

namespace App\Services;

use App\Models\User;

final class ProfileCompletenessService
{
    /**
     * @return array{
     *   score: int,
     *   percent: int,
     *   max: int,
     *   missing: list<array{key:string,label:string,href:string,weight:int}>,
     *   items: list<array{key:string,label:string,done:bool,weight:int,href:string}>
     * }
     */
    public function forUser(User $user): array
    {
        $items = [
            [
                'key' => 'photo',
                'label' => 'Profil fotoğrafı',
                'done' => filled($user->profile_photo_url),
                'weight' => 30,
                'href' => route('profile'),
            ],
            [
                'key' => 'birth_date',
                'label' => 'Doğum tarihi',
                'done' => filled($user->birth_date),
                'weight' => 15,
                'href' => route('profile'),
            ],
            [
                'key' => 'city',
                'label' => 'Şehir',
                'done' => filled($user->city),
                'weight' => 15,
                'href' => route('profile'),
            ],
            [
                'key' => 'bio',
                'label' => 'Kısa bio',
                'done' => filled($user->bio) && mb_strlen(trim((string) $user->bio)) >= 20,
                'weight' => 15,
                'href' => route('profile'),
            ],
            [
                'key' => 'relationship_status',
                'label' => 'İlişki durumu',
                'done' => filled($user->relationship_status),
                'weight' => 10,
                'href' => route('profile'),
            ],
            [
                'key' => 'expectation',
                'label' => 'İlişki beklentisi',
                'done' => filled($user->relationship_expectation),
                'weight' => 10,
                'href' => route('profile'),
            ],
            [
                'key' => 'hobbies',
                'label' => 'İlgi alanları',
                'done' => count($user->resolvedHobbies()) >= 2,
                'weight' => 5,
                'href' => route('profile'),
            ],
        ];

        $score = 0;
        $max = 0;
        $missing = [];

        foreach ($items as $item) {
            $max += $item['weight'];
            if ($item['done']) {
                $score += $item['weight'];
            } else {
                $missing[] = [
                    'key' => $item['key'],
                    'label' => $item['label'],
                    'href' => $item['href'],
                    'weight' => $item['weight'],
                ];
            }
        }

        return [
            'score' => $score,
            'max' => $max,
            'percent' => $max > 0 ? (int) round(($score / $max) * 100) : 0,
            'missing' => $missing,
            'items' => $items,
        ];
    }
}
