<?php

namespace App\Filament\Teacher\Widgets;

use App\Models\ActivitySubmission;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TeacherStatsOverview extends BaseWidget
{
    // Agar widget tidak ter-cache saat data berubah
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $menungguKoreksi = \App\Models\ActivitySubmission::where('status', 'menunggu_koreksi')->count();
        $sudahDinilai = \App\Models\ActivitySubmission::where('status', 'dinilai')->count();
        
        // 👇 PERBAIKAN DI SINI: Menghitung ID Murid unik yang sudah pernah mengumpulkan tugas 👇
        $muridAktif = \App\Models\ActivitySubmission::distinct('student_id')->count('student_id');

        return [
            Stat::make('Perlu Dikoreksi', $menungguKoreksi)
                ->description($menungguKoreksi > 0 ? '⚠️ Ada tugas menumpuk!' : '✅ Meja Anda bersih')
                ->descriptionIcon($menungguKoreksi > 0 ? 'heroicon-m-exclamation-circle' : 'heroicon-m-check-circle')
                ->color($menungguKoreksi > 0 ? 'danger' : 'success')
                ->chart($menungguKoreksi > 0 ? [7, 3, 4, 5, 6, 3, 5, 3] : null),

            Stat::make('Selesai Dinilai', $sudahDinilai)
                ->description('Total aktivitas dikoreksi')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            Stat::make('Murid Aktif', $muridAktif)
                ->description('Telah mengumpulkan tugas')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
        ];
    }
}