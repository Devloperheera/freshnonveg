<?php

namespace App\Support;

class UploadRules
{
    /** @return list<string> */
    public static function image(bool $required = true): array
    {
        $rules = ['image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'];

        array_unshift($rules, $required ? 'required' : 'nullable');

        return $rules;
    }
}
