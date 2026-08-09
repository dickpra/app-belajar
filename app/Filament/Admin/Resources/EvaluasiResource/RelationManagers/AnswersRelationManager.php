<?php

namespace App\Filament\Admin\Resources\EvaluasiResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class AnswersRelationManager extends RelationManager
{
    protected static string $relationship = 'answers';
    protected static ?string $title = 'Rincian Jawaban Murid';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question.activity.module.title')
                    ->label('Sumber Modul')
                    ->description(fn ($record) => Str::limit(strip_tags($record->question->activity->title ?? ''), 30))
                    ->limit(20)
                    ->sortable(),

                Tables\Columns\TextColumn::make('question.question_text')
                    ->label('Pertanyaan')
                    ->formatStateUsing(fn ($state) => strip_tags($state))
                    ->wrap(),

                Tables\Columns\TextColumn::make('answer_value')
                    ->label('Jawaban Diberikan')
                    ->weight('bold'),

                // MENAMPILKAN INDIKATOR BENAR / SALAH / MANUAL
                Tables\Columns\TextColumn::make('is_correct')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        true => 'success',
                        false => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        true => 'Benar ✅',
                        false => 'Salah ❌',
                        default => 'Cek Manual ⚠️',
                    }),
            ])
            ->headerActions([
            Tables\Actions\Action::make('reset_modul')
                ->label('🔄 Reset Jawaban Modul Ini')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reset Jawaban Murid?')
                ->modalDescription('Apakah Anda yakin ingin menghapus semua jawaban murid ini di modul terkait? Murid harus mengulang mengerjakan dari awal.')
                ->action(function (\Filament\Resources\RelationManagers\RelationManager $livewire) {
                    // Ambil ID Murid dari relasi halaman saat ini
                    $studentId = $livewire->ownerRecord->id;
                    
                    // Eksekusi: Hapus semua jawaban murid ini
                    // (Anda bisa memfilter berdasarkan modul_id jika ingin lebih spesifik)
                    \App\Models\StudentAnswer::where('student_id', $studentId)->delete();
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Jawaban Berhasil Direset!')
                        ->success()
                        ->send();
                }),
        ])
            // Kelompokkan tabel otomatis berdasarkan aktivitas
            ->defaultGroup('question.activity.title')
            ->defaultSort('created_at', 'asc');
    }
}
