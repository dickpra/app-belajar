<?php

return [
    'name' => 'Ruang Belajar',
    'manifest' => [
        'name' => env('APP_NAME', 'Ruang Belajar'),
        'short_name' => 'Belajar',
        
        // Langsung arahkan ke Dashboard agar tidak muter ke Login
        'start_url' => '/', 
        
        // Warna tema disesuaikan dengan warna kuning aplikasi Anda
        'background_color' => '#FFFBEB',
        'theme_color' => '#f59e0b',
        
        'display' => 'standalone',
        'orientation'=> 'portrait',
        'status_bar'=> 'default',
        
        // KITA HANYA PAKAI 1 UKURAN LOGO (512x512)
        'icons' => [
            '512x512' => [
                'path' => '/images/icons/icon-512x512.png',
                'purpose' => 'any maskable'
            ],
        ],
        
        // Splash screen dan shortcut kita kosongkan agar ringan
        'splash' => [],
        'shortcuts' => [],
        'custom' => []
    ]
];