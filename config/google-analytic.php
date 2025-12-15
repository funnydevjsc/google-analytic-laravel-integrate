<?php

return [
    'tag_manager_id' => env('GOOGLE_TAG_MANAGER_ID'),
    'property_id' => env('GOOGLE_ANALYTIC_PROPERTY_ID'),
    'measurement_id' => env('GOOGLE_ANALYTIC_MEASUREMENT_ID'),
    'measurement_api_secret' => env('GOOGLE_ANALYTIC_MEASUREMENT_API_SECRET'),
    'verification_key' => env('GOOGLE_ANALYTIC_SITE_VERIFICATION_KEY'),

    /*
     |--------------------------------------------------------------------------
     | User identity & properties (GA4)
     |--------------------------------------------------------------------------
     |
     | - user_id: should be a stable, non-PII identifier (e.g. internal user id/uuid).
     | - user_properties: custom user properties for reporting/audiences (non-PII).
     |
     | NOTE: Google Analytics policies prohibit sending PII (even hashed) to GA.
     */
    'user_id_fields' => ['uuid', 'id'],

    // Optional callback: function (?\Illuminate\Contracts\Auth\Authenticatable $user, \Illuminate\Http\Request $request): ?string
    'user_id_resolver' => null,

    // Map user properties from Auth user attributes using dot-notation.
    // Example: ['customer_type' => 'type', 'plan' => 'plan', 'is_paying' => 'is_paying']
    'user_properties' => [],

    // Optional callback: function (?\Illuminate\Contracts\Auth\Authenticatable $user, \Illuminate\Http\Request $request): array
    'user_properties_resolver' => null,
];