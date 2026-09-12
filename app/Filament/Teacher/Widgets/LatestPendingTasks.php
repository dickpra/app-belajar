<?php

namespace App\Filament\Teacher\Widgets;

use App\Models\ActivitySubmission;
use App\Filament\Teacher\Resources\EvaluationResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestPendingTasks extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    // Urutkan widget ini agar berada di bawah statistik
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Hanya ambil data yang belum dikoreksi
                ActivitySubmission::query()
                    ->where('status', 'menunggu_koreksi')
                    ->latest()
            )
            ->heading('Antrean Koreksi Terkini')
            ->description('Daftar murid yang baru saja mengumpulkan tugas dan menunggu penilaian Anda.')
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Nama Murid')
                    ->weight('bold')
                    ->size('lg')
                    ->searchable(),

                Tables\Columns\TextColumn::make('activity.title')
                    ->label('Aktivitas yang Dikerjakan')
                    ->description(fn ($record) => $record->activity->module->title ?? '-')
                    ->limit(40),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Pengumpulan')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => $state->diffForHumans()),
            ])
            ->actions([
                Tables\Actions\Action::make('koreksi')
                    ->label('Buka Koreksi')
                    ->icon('heroicon-o-pencil-square')
                    ->button()
                    ->color('success')
                    ->url(fn (ActivitySubmission $record): string => 
                        // Arahkan langsung ke halaman "Buku Penilaian" milik murid tersebut
                        EvaluationResource::getUrl('edit', ['record' => $record->student_id])
                    ),
            ])
            ->emptyStateHeading('Tidak Ada Antrean')
            ->emptyStateDescription('Semua tugas murid sudah beres Anda nilai. Waktunya bersantai!')
            ->emptyStateIcon('heroicon-o-face-smile');
    }
}