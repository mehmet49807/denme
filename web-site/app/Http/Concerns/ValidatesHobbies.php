<?php

namespace App\Http\Concerns;

use App\Support\HobbyCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

trait ValidatesHobbies
{
    /**
     * @return array{hobbies: list<string>}
     */
    protected function validateHobbiesInput(Request $request, bool $required = false): array
    {
        $max = HobbyCatalog::max();
        $rules = [
            'hobbies' => ($required ? 'required' : 'nullable').'|array|max:'.$max,
            'hobbies.*' => ['string', Rule::in(HobbyCatalog::ids())],
        ];

        $request->validate($rules, [
            'hobbies.max' => 'En fazla '.$max.' hobi seçebilirsiniz.',
            'hobbies.*.in' => 'Geçersiz hobi seçimi.',
        ]);

        return [
            'hobbies' => HobbyCatalog::normalize($request->input('hobbies', [])),
        ];
    }
}
