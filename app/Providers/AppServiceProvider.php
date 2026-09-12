<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView;

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
