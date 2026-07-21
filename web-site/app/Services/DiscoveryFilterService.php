<?php

namespace App\Services;

use App\Support\RelationshipStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class DiscoveryFilterService
{
    /**
     * @return array{
     *   q: string,
     *   age_min: ?int,
     *   age_max: ?int,
     *   city: string,
     *   district: string,
     *   online: bool,
     *   with_photo: bool,
     *   relationship_status: string,
     *   relationship_expectation: string,
     *   active: bool
     * }
     */
    public function parse(Request $request): array
    {
        $ageMin = $request->integer('age_min') ?: null;
        $ageMax = $request->integer('age_max') ?: null;

        if ($ageMin !== null) {
            $ageMin = max(18, min(80, $ageMin));
        }
        if ($ageMax !== null) {
            $ageMax = max(18, min(80, $ageMax));
        }
        if ($ageMin !== null && $ageMax !== null && $ageMin > $ageMax) {
            [$ageMin, $ageMax] = [$ageMax, $ageMin];
        }

        $status = trim((string) $request->query('relationship_status', ''));
        if ($status !== '' && ! RelationshipStatus::isValid($status)) {
            $status = '';
        }

        $expectation = trim((string) $request->query('relationship_expectation', ''));
        if (mb_strlen($expectation) > 80) {
            $expectation = mb_substr($expectation, 0, 80);
        }

        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) > 80) {
            $q = mb_substr($q, 0, 80);
        }

        $city = trim((string) $request->query('city', ''));
        if (mb_strlen($city) > 80) {
            $city = mb_substr($city, 0, 80);
        }

        $district = trim((string) $request->query('district', ''));
        if (mb_strlen($district) > 80) {
            $district = mb_substr($district, 0, 80);
        }

        return [
            'q' => $q,
            'age_min' => $ageMin,
            'age_max' => $ageMax,
            'city' => $city,
            'district' => $district,
            'online' => $request->boolean('online'),
            'with_photo' => $request->boolean('with_photo'),
            'relationship_status' => $status,
            'relationship_expectation' => $expectation,
            'active' => $q !== ''
                || $ageMin !== null
                || $ageMax !== null
                || $city !== ''
                || $district !== ''
                || $request->boolean('online')
                || $request->boolean('with_photo')
                || $status !== ''
                || $expectation !== '',
        ];
    }

    /**
     * @param  Builder<\App\Models\User>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<\App\Models\User>
     */
    public function apply(Builder $query, array $filters): Builder
    {
        $q = (string) ($filters['q'] ?? '');
        if ($q !== '') {
            $like = '%'.addcslashes($q, '%_\\').'%';
            $query->where(function ($builder) use ($like) {
                $builder->where('username', 'like', $like)
                    ->orWhere('city', 'like', $like)
                    ->orWhere('district', 'like', $like)
                    ->orWhere('first_name', 'like', $like);
            });
        }

        $city = trim((string) ($filters['city'] ?? ''));
        if ($city !== '') {
            $query->where('city', $city);
        }

        $district = trim((string) ($filters['district'] ?? ''));
        if ($district !== '') {
            $query->where('district', $district);
        }

        $ageMin = $filters['age_min'] ?? null;
        if (is_int($ageMin)) {
            $query->whereNotNull('birth_date')
                ->where('birth_date', '<=', now()->subYears($ageMin)->toDateString());
        }

        $ageMax = $filters['age_max'] ?? null;
        if (is_int($ageMax)) {
            $query->whereNotNull('birth_date')
                ->where('birth_date', '>=', now()->subYears($ageMax + 1)->addDay()->toDateString());
        }

        if (! empty($filters['with_photo'])) {
            $query->whereNotNull('profile_photo_url')
                ->where('profile_photo_url', '!=', '');
        }

        if (! empty($filters['online'])) {
            $minutes = (int) (\App\Models\User::ONLINE_MINUTES ?? 15);
            $query->whereNotNull('last_active_at')
                ->where('last_active_at', '>=', now()->subMinutes($minutes));
        }

        $status = (string) ($filters['relationship_status'] ?? '');
        if ($status !== '') {
            $query->where('relationship_status', $status);
        }

        $expectation = trim((string) ($filters['relationship_expectation'] ?? ''));
        if ($expectation !== '') {
            $like = '%'.addcslashes($expectation, '%_\\').'%';
            $query->where('relationship_expectation', 'like', $like);
        }

        return $query;
    }
}
