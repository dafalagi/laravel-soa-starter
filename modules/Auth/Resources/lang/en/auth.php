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

    'register' => [
        'success' => 'User registered successfully.',
        'failed' => 'Registration failed. Please try again.',
        'email_exists' => 'The email address is already registered.',
        'password_mismatch' => 'Password confirmation does not match.',
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

    'validation' => [
        'email_required' => 'Email address is required.',
        'email_invalid' => 'Please provide a valid email address.',
        'password_required' => 'Password is required.',
        'password_min' => 'Password must be at least 8 characters.',
        'name_required' => 'Name is required.',
        'name_max' => 'Name may not be greater than 255 characters.',
    ],

    'errors' => [
        'unauthorized' => 'You are not authorized to perform this action.',
        'unauthenticated' => 'Please log in to continue.',
        'forbidden' => 'Access forbidden.',
        'user_not_found' => 'User not found.',
        'service_unavailable' => 'Authentication service is temporarily unavailable.',
    ],
];
