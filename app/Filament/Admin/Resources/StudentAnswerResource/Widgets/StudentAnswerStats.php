<?php

namespace App\Filament\Admin\Resources\StudentAnswerResource\Widgets;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\StudentAnswer;
use App\Models\Student;

class StudentAnswerStats extends BaseWidget
{
    protected function getStats(): array
    {
        // Menghitung statistik langsung dari database
        $totalJawaban = StudentAnswer::count();
        
        // Menghitung jumlah murid unik yang sudah menyetor jawaban
        $muridAktif = StudentAnswer::distinct('student_id')->count('student_id');

        return [
            Stat::make('Total Jawaban Masuk', $totalJawaban)
                ->description('Seluruh butir soal yang dijawab')
                ->descriptionIcon('heroicon-m-inbox-arrow-down')
                ->color('success'),
                
            Stat::make('Murid Aktif', $muridAktif . ' Anak')
                ->description('Yang sudah mulai mengerjakan tugas')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Status Sistem', 'Otomatis Tersimpan')
                ->description('Aman dari putus koneksi')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('warning'),
        ];
    }
}