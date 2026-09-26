<?php

namespace App\Filament\Widgets;

use App\Models\ActivitySubmission;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestSubmissions extends BaseWidget
{
    // Opsional: Buat tabel mengambil lebar penuh halaman
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Mengambil data submission terbaru beserta relasi murid dan aktivitasnya
                ActivitySubmission::query()->with(['student', 'activity'])->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Nama Murid')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('activity.title')
                    ->label('Aktivitas yang Dikerjakan')
                    ->limit(40),

                // Catatan: Jika Anda menggunakan Filament v3, BadgeColumn diganti dengan TextColumn::make('status')->badge()->color(...)
                // Namun ini dipertahankan sesuai kode Anda (Filament v2)
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status Pengerjaan')
                    ->colors([
                        'warning' => 'mengerjakan',
                        'success' => 'dinilai',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Selesai')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->paginated(false); // Matikan paginasi agar widget tetap ringkas
    }
}