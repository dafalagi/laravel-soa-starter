<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the Auth module for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'login' => [
        'success' => 'User logged in successfully.',
        'failed' => 'The provided credentials are incorrect.',
        'invalid_credentials' => 'Invalid credentials provided.',
        'account_inactive' => 'Your account is currently inactive.',
        'too_many_attempts' => 'Too many login attempts. Please try again later.',
    ],

    'logout' => [
        'success' => 'User logged out successfully.',
        'failed' => 'Logout failed. Please try again.',
    ],

    'token' => [
        'refresh_success' => 'Token refreshed successfully.',
        'refresh_failed' => 'Token refresh failed.',
        'invalid' => 'Invalid or expired token.',
        'missing' => 'Authentication token is missing.',
    ],
];
