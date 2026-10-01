<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\File;
use Filament\Notifications\Notification;

class PengaturanGuru extends Page implements HasForms
{
    use InteractsWithForms;

    // Pengaturan Ikon dan Nama Menu di Sidebar Admin
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Ucapan Guru';
    protected static ?string $title = 'Pengaturan Ucapan Guru';
    protected static ?string $navigationGroup = 'Pengaturan';

    // Arahkan ke file tampilan (Blade) kustom kita
    protected static string $view = 'filament.admin.pages.pengaturan-guru';

    public ?array $data = [];

    // Fungsi ini berjalan saat halaman pertama kali dibuka
    public function mount(): void
    {
        $teks = '';
        $path = storage_path('app/ucapan_guru.txt');
        
        // Cek apakah file txt sudah ada, jika ada baca isinya
        if (File::exists($path)) {
            $teks = File::get($path);
        }

        // Isi form dengan teks dari file
        $this->form->fill([
            'ucapan' => $teks,
        ]);
    }

    // Skema Form Input
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('ucapan')
                    ->label('Teks Ucapan untuk Murid')
                    ->rows(5)
                    ->required()
                    ->helperText('Teks ini akan langsung muncul di HP murid pada menu "Ucapan Guru".'),
            ])
            ->statePath('data');
    }

    // Fungsi untuk tombol Simpan
    public function simpan(): void
    {
        $data = $this->form->getState();
        $path = storage_path('app/ucapan_guru.txt');

        // Tulis teks langsung ke file .txt (TIDAK PERLU DATABASE!)
        File::put($path, $data['ucapan']);

        // Munculkan notifikasi sukses di pojok kanan atas
        Notification::make()
            ->title('Berhasil Disimpan!')
            ->body('Pesan semangat untuk murid telah diperbarui.')
            ->success()
            ->send();
    }
}