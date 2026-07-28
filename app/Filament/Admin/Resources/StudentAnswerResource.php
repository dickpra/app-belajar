<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StudentAnswerResource\Pages;
use App\Filament\Admin\Resources\StudentAnswerResource\RelationManagers;
use App\Models\StudentAnswer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Grouping\Group;
use Illuminate\Support\Str;

class StudentAnswerResource extends Resource
{
    protected static ?string $model = StudentAnswer::class;
    
    // Ganti ikon menjadi daftar centang (checklist)
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Manajemen Jawaban';
    protected static ?string $pluralModelLabel = 'Data Jawaban Murid';
    protected static ?string $navigationGroup = 'Evaluasi Pembelajaran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Jawaban')
                    ->schema([
                        // Form dibuat disabled (hanya baca) agar admin tidak salah edit relasi
                        Forms\Components\Select::make('student_id')
                            ->relationship('student', 'name')
                            ->label('Nama Murid')
                            ->disabled(),
                            
                        Forms\Components\Select::make('question_id')
                            ->relationship('question', 'id')
                            ->label('ID Soal')
                            ->disabled(),
                            
                        Forms\Components\Textarea::make('answer_value')
                            ->label('Jawaban yang Diberikan')
                            ->required(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question.activity.module.title')
                    ->label('Dari Modul')
                    ->limit(20)
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    // Memunculkan nama aktivitas sebagai baris kedua agar hemat tempat
                    ->description(fn ($record) => Str::limit($record->question->activity->title, 25)),

                Tables\Columns\TextColumn::make('question.question_text')
                    ->label('Pertanyaan / Soal')
                    ->formatStateUsing(fn ($state) => strip_tags($state))
                    ->limit(40)
                    ->searchable()
                    ->wrap(), // Wrap agar teks panjang turun ke bawah, tidak terpotong

                Tables\Columns\TextColumn::make('answer_value')
                    ->label('Jawaban Murid')
                    ->searchable()
                    ->badge()
                    ->color('success'), 

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Waktu Pengerjaan')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(), // Bisa disembunyikan oleh guru jika layar terlalu penuh
            ])
            ->filters([
                // Filter tambahan jika dibutuhkan nanti
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            // INI KUNCI UTAMANYA: PENGELOMPOKAN DATA
            ->groups([
                Group::make('student.name')
                    ->label('Berdasarkan Murid')
                    ->collapsible(), // Membuat grup bisa di-klik untuk buka/tutup (collapse)
                    
                Group::make('question.activity.module.title')
                    ->label('Berdasarkan Modul')
                    ->collapsible(),
            ])
            // Atur defaultnya mengelompok berdasarkan Murid
            ->defaultGroup('student.name')
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageStudentAnswers::route('/'),
        ];
    }
}
