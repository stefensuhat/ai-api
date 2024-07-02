<?php

return [
    'key' => env('SUPABASE_KEY'),
    'url' => env('SUPABASE_URL', 'http://localhost:54321'),
    'auth_url' => env('SUPABASE_URL').env('SUPABASE_AUTH_URL', '/auth/v1'),

];
