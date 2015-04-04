<?php

return [
    'plugin' => [
        'name' => 'Social',
        'description' => 'OAuth2 based social login provider.'
    ],
    'errors' => [
        'redirect_uri_mismatch' => 'The redirect setting for this login is misconfigured. Set it to ',
        'used_token' => 'This login token has already been used!',
        'user_denied' => 'Cancelled by user.',
        'access_denied' => 'You need to approve the access in order to login.',
        'incorrect_client_credentials' => 'The client_id and / or client_secret passed are incorrect.',
        'bad_verification_code' => 'The code passed is incorrect or expired.',
        'error_occured' => 'Error Occured'
    ],
    'component' => [
        'btn_size' => 'Button Size',
        'btn_desc' => 'The size of the social button',
        'facebook_login'  => 'Facebook Login',
        'facebook_desc'   => 'Insert a Facebook Login Button to page',
        'github_login'    => 'Github Login',
        'github_desc'     => 'Insert a Github Login Button to page',
        'google_login'    => 'Google Login',
        'google_desc'     => 'Insert a Google Login Button to page',
        'linkedin_login'  => 'LinkedIn Login',
        'linkedin_desc'   => 'Insert a LinkedIn Login Button to page',
        'microsoft_login' => 'Microsoft Login',
        'microsoft_desc'  => 'Insert a Microsoft Login Button to page',
    ],
    'settings' => [
        'social' => 'Social',
        'description' => 'Manage social login providers.'
    ],
];