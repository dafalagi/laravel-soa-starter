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
        'inactive_account' => 'Your account is currently inactive.',
        'too_many_attempts' => 'Too many login attempts. Please try again later.',
    ],

    'logout' => [
        'success' => 'User logged out successfully.',
        'failed' => 'Logout failed. Please try again.',
    ],

    'token' => [
        'refresh_success' => 'Token refreshed successfully.',
        'inactive_account' => 'Cannot refresh token for inactive account.',
    ],
];
