<?php

use Illuminate\Support\Str;

if (!function_exists('generateUuid'))
{
    function generateUuid()
    {
        return Str::uuid7();
    }
}

if (!function_exists('generateRandomString'))
{
    function generateRandomString($length = 10)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters_length = strlen($characters);
        $random_string = '';

        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[rand(0, $characters_length - 1)];
        }

        return $random_string;
    }
}

if (!function_exists('generateOrderNumber'))
{
    function generateOrderNumber($id, $zero_count = 5)
    {
        return str_pad($id, $zero_count , '0', STR_PAD_LEFT);
    }
}
