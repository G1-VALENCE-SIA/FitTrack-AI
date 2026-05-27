<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | FitTrack-AI EXTERNAL APIs
    |--------------------------------------------------------------------------
    */

    'exercise_db' => [
        'base_url' => env('EXERCISEDB_BASE_URL'),
        'api_key' => env('EXERCISEDB_API_KEY'),
        'host' => env('EXERCISEDB_API_HOST'),
    ],

    'edamam' => [
        'base_url' => env('EDAMAM_BASE_URL'),
        'api_key' => env('EDAMAM_API_KEY'),
        'host' => env('EDAMAM_API_HOST'),
    ],

    'bmi' => [
        'base_url' => env('BMI_BASE_URL'),
        'api_key' => env('BMI_API_KEY'),
        'host' => env('BMI_API_HOST'),
    ],

    'weather' => [
        'base_url' => env('WEATHER_BASE_URL'),
        'api_key' => env('WEATHER_API_KEY'),
        'host' => env('WEATHER_API_HOST'),
    ],

    'quotes' => [
        'base_url' => env('QUOTES_BASE_URL'),
        'api_key' => env('QUOTES_API_KEY'),
    ],

];