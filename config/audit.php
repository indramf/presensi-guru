<?php

return [
    // Kunci yang selalu dibuang dari before/after sebelum disimpan (NFR-05).
    'sensitive_keys' => [
        'password', 'password_confirmation', 'current_password',
        'remember_token', 'token', 'api_token', 'secret', 'app_key',
    ],

    // Opsional. Kosong = SHA-256 biasa. Diisi = HMAC-SHA256.
    'hmac_key' => env('AUDIT_HMAC_KEY'),

    // Checkpoint di luar database.
    'checkpoint_path' => storage_path('app/audit/checkpoint.json'),
];
