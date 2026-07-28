<?php

namespace App\Filament\Admin\Resources\EvaluasiResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;


class EvaluasiStats extends BaseWidget
{
    // Menangkap data murid yang sedang diklik
    public ?Model $record = null; 

    protected function getStats(): array
    {
        if (!$this->record) return [];

        $answers = $this->record->answers;
        $total = $answers->count();
        $benar = $answers->whereStrict('is_correct', true)->count();
        $salah = $answers->whereStrict('is_correct', false)->count();
        $cekManual = $answers->whereStrict('is_correct', null)->count();

        // Hitung persentase nilai (Hanya dari soal yang bisa dikoreksi otomatis)
        $soalOtomatis = $benar + $salah;
        $nilai = $soalOtomatis > 0 ? round(($benar / $soalOtomatis) * 100) : 0;

        return [
            Stat::make('Skor Pilihan Ganda', $nilai . ' / 100')
                ->description($nilai >= 70 ? 'Lulus KKM' : 'Perlu Belajar Lagi')
                ->descriptionIcon($nilai >= 70 ? 'heroicon-m-hand-thumb-up' : 'heroicon-m-hand-thumb-down')
                ->color($nilai >= 70 ? 'success' : 'danger'),
                
            Stat::make('Statistik Jawaban', $benar . ' Benar, ' . $salah . ' Salah')
                ->description('Dari total ' . $soalOtomatis . ' soal pilihan ganda')
                ->color('info'),
                
            Stat::make('Tugas Praktik (Isian)', $cekManual . ' Soal')
                ->description('Membutuhkan koreksi manual dari Guru')
                ->color('warning'),
        ];
    }
}