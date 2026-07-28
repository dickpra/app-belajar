<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EvaluasiResource\Pages;
use App\Filament\Admin\Resources\EvaluasiResource\RelationManagers;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Grouping\Group;

class EvaluasiResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Rapor & Evaluasi';
    protected static ?string $pluralModelLabel = 'Rapor Murid';
    protected static ?string $navigationGroup = 'Evaluasi Pembelajaran';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Murid')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('answers_count')
                    ->counts('answers') // Menghitung otomatis dari relasi
                    ->label('Total Soal Dikerjakan')
                    ->badge()
                    ->color('info'),
            ])
            ->actions([
                // Tombol View (Lihat) untuk masuk ke halaman detail
                Tables\Actions\ViewAction::make()->label('Buka Rapor'),
            ]);
    }

    public static function getRelations(): array
    {
        // Menyambungkan tabel detail jawaban ke dalam halaman profil murid
        return [
            RelationManagers\AnswersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvaluasis::route('/'),
            'view' => Pages\ViewEvaluasi::route('/{record}'), // Halaman Detail
        ];
    }
}