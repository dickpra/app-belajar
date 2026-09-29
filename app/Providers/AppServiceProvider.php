<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\URL;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

   public function boot(): void
    {
        // Paksa semua URL (termasuk Livewire) menggunakan HTTPS di lingkungan produksi
        if (config('app.env') === 'production' || str_contains(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
        // Menyuntikkan CSS langsung ke dalam <head> Filament
        \Filament\Support\Facades\FilamentView::registerRenderHook(
            'panels::head.end',
            fn (): string => '<style>
                /* 1. Sembunyikan nama file dan ukuran bawaan Trix */
                trix-editor .attachment__name,
                trix-editor .attachment__size {
                    display: none !important;
                }
                
                /* 2. Matikan sifat link (hyperlink) pada gambar dan caption */
                trix-editor figure.attachment a {
                    pointer-events: none !important; /* Mencegahnya bisa diklik sebagai link */
                    text-decoration: none !important; /* Menghilangkan garis bawah link */
                    color: inherit !important; /* Mengembalikan warna teks ke normal (hitam/abu) */
                    cursor: text !important;
                }
                
                /* 3. Pastikan area caption tetap bisa diklik untuk mengetik */
                trix-editor figure.attachment figcaption {
                    pointer-events: auto !important;
                }
            </style>'
        );
    }
}
