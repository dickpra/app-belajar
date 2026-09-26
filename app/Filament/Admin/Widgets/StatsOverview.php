<?php

namespace App\Filament\Widgets;

use App\Models\Module;
use App\Models\Activity;
use App\Models\ActivitySubmission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Mendefinisikan variabel yang terpotong sebelumnya
        $totalModul = Module::count();
        $modulAktif = Module::where('is_active', true)->count();
        $totalDinilai = ActivitySubmission::where('status', 'dinilai')->count();

        return [
            Stat::make('Total Modul Pembelajaran', $totalModul)
                ->description($modulAktif . ' modul sedang aktif / publish')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17]), // Grafik visual (opsional)

            Stat::make('Total Aktivitas & Latihan', Activity::count())
                ->description('Materi dan evaluasi di dalam modul')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),

            Stat::make('Tugas Murid Dinilai', $totalDinilai)
                ->description('Total aktivitas yang telah diselesaikan murid')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success')
                ->chart([1, 5, 2, 8, 12, 15, 20]), 
        ];
    }
}