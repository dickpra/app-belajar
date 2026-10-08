<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SignDictionaryResource\Pages;
use App\Filament\Admin\Resources\SignDictionaryResource\RelationManagers;
use App\Models\SignDictionary;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class SignDictionaryResource extends Resource
{
    protected static ?string $model = SignDictionary::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('word')
                ->label('Kosakata')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                // Otomatis Kapital di awal kata (Misal: "buku" -> "Buku")
                ->mutateDehydratedStateUsing(fn ($state) => Str::title($state)),

            Forms\Components\FileUpload::make('video_path')
                ->label('Video Bahasa Isyarat')
                ->required()
                ->acceptedFileTypes(['video/mp4', 'video/webm'])
                ->disk('modul_rahasia') // Memakai disk private Anda
                ->directory('kamus_isyarat')
                ->maxSize(51200), // Maksimal 50MB
        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('word')
                ->label('Kosakata')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->defaultSort('word', 'asc') // Otomatis urut A-Z di Admin
        ->filters([])
        ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
}

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSignDictionaries::route('/'),
            'create' => Pages\CreateSignDictionary::route('/create'),
            'edit' => Pages\EditSignDictionary::route('/{record}/edit'),
        ];
    }
}
