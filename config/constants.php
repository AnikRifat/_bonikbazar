<?php

//Sandbox
defined('business') or define('business', 'sb-uefcv23946367@business.example.com');

//Live
// defined('PAYPAL_LIVE_BUSINESS_EMAIL') or define('PAYPAL_LIVE_BUSINESS_EMAIL', '');
// defined('PAYPAL_CURRENCY') or define('PAYPAL_CURRENCY', 'USD');

return [
    'RESPONSE_CODE' => [
        'LOGIN_SUCCESS'    => 100,
        'VALIDATION_ERROR' => 102,
        'EXCEPTION_ERROR'  => 103,
        'SUCCESS'          => 200,
    ]
];
