<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\QuestionResource\Pages;
use App\Models\Question;
use App\Models\Activity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Manajemen Soal';
    protected static ?int $navigationSort = 2; // Agar posisinya di bawah Modul

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Penempatan Soal')
                    ->schema([
                        Forms\Components\Select::make('activity_id')
                            ->label('Pilih Modul & Aktivitas')
                            ->options(function () {
                                return Activity::with('module')->get()->mapWithKeys(function ($act) {
                                    $modTitle = $act->module ? $act->module->title : 'Tanpa Modul';
                                    return [$act->id => "{$modTitle} ➔ {$act->title}"];
                                });
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),

                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Select::make('answer_format')
                        ->options([
                            'multiple_choice'       => 'Pilihan Ganda (Teks / Gambar)',
                            'number_input'          => 'Input Angka Tunggal',
                            'text_input'            => 'Input Teks Singkat',
                            'matching'              => 'Menjodohkan (Kiri & Kanan)',
                            'true_false_correction' => 'Benar/Salah + Teks Perbaikan',
                            'complex_fill'          => 'Isian Rumpang (Banyak Titik-titik)',
                        ])
                        ->required()
                        ->live()
                        ->label('Tipe Jawaban'),

                    Forms\Components\Select::make('layout_position')
                        ->options([
                            'image_left'   => 'Gambar Utama di Kiri',
                            'image_right'  => 'Gambar Utama di Kanan',
                            'image_top'    => 'Gambar Utama di Atas',
                            'image_bottom' => 'Gambar Utama di Bawah',
                        ])
                        ->default('image_top')
                        ->required()
                        ->label('Posisi Gambar Utama'),
                    
                    Forms\Components\Select::make('difficulty')
                        ->options([
                            'easy' => '🌟 Rendah (Mudah)',
                            'medium' => '⭐⭐ Sedang (Menengah)',
                            'hard'  => '🔥 Sulit (HOTS)',
                        ])
                        ->default('medium')
                        ->required()
                        ->label('Tingkat Kesulitan Soal'),
                ]),

                Forms\Components\Section::make('Konten Pertanyaan')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->optimize('webp')
                            ->imageEditor()
                            ->disk('modul_rahasia')
                            ->visibility('private')
                            ->directory('modul_private/soal_thumbnail')
                            ->label('Gambar Utama Soal (Opsional)'),

                        Forms\Components\FileUpload::make('sign_language_video')
                            ->label('🤟 Video Bahasa Isyarat Soal (Opsional)')
                            ->disk('modul_rahasia')
                            ->visibility('private')
                            ->directory('modul_private/video_soal')
                            ->acceptedFileTypes(['video/mp4', 'video/webm'])
                            ->maxSize(51200)
                            ->previewable(false),

                        Forms\Components\RichEditor::make('question_text')
                            ->label('Teks Pertanyaan (Bisa sisip banyak gambar)')
                            ->fileAttachmentsDisk('modul_rahasia')
                            ->fileAttachmentsVisibility('private')
                            ->fileAttachmentsDirectory('modul_private/soal_sisipan')
                            ->toolbarButtons(['bold', 'italic', 'underline', 'strike', 'link', 'h3', 'bulletList', 'orderedList', 'attachFiles'])
                            ->required(),
                    ])->columns(1),
                
                Forms\Components\Section::make('Kunci Jawaban & Pembahasan')
                    ->schema([
                        Forms\Components\TextInput::make('correct_answer')
                            ->label('Kunci Jawaban Pasti')
                            ->placeholder('Contoh: 25 atau "Matahari"')
                            ->visible(fn (\Filament\Forms\Get $get) => in_array($get('answer_format'), ['number_input', 'text_input']))
                            ->required(fn (\Filament\Forms\Get $get) => in_array($get('answer_format'), ['number_input', 'text_input'])),

                        Forms\Components\Select::make('true_false_answer')
                            ->label('Kunci Jawaban yang Tepat')
                            ->options([
                                'Benar' => 'Pernyataan ini BENAR',
                                'Salah' => 'Pernyataan ini SALAH',
                            ])
                            ->visible(fn (\Filament\Forms\Get $get) => $get('answer_format') === 'true_false_correction')
                            ->required(fn (\Filament\Forms\Get $get) => $get('answer_format') === 'true_false_correction')
                            ->live(),

                        Forms\Components\TextInput::make('correction_text')
                            ->label('Teks Perbaikan (Wajib jika SALAH)')
                            ->visible(fn (\Filament\Forms\Get $get) => $get('answer_format') === 'true_false_correction' && $get('true_false_answer') === 'Salah')
                            ->required(fn (\Filament\Forms\Get $get) => $get('answer_format') === 'true_false_correction' && $get('true_false_answer') === 'Salah'),

                        Forms\Components\Placeholder::make('pg_notice')
                            ->label('Cara Menentukan Kunci Jawaban')
                            ->content(function (\Filament\Forms\Get $get) {
                                return match ($get('answer_format')) {
                                    'multiple_choice' => '👇 Tambahkan opsi di bawah, centang "⭐ Ini Jawaban Benar".',
                                    'matching' => '👇 Tuliskan pasangan BENAR secara sejajar di kolom Kiri dan Kanan pada bagian opsi di bawah. Sistem akan mengacak otomatis.',
                                    'complex_fill' => '👇 Tambahkan daftar kata jawaban yang benar secara BERURUTAN dari atas ke bawah di bagian opsi.',
                                    default => 'Pilih tipe jawaban terlebih dahulu di atas.',
                                };
                            })
                            ->visible(fn (\Filament\Forms\Get $get) => in_array($get('answer_format'), ['multiple_choice', 'matching', 'complex_fill'])),

                        Forms\Components\RichEditor::make('answer_explanation')
                            ->label('Catatan Pembahasan (Opsional)')
                            ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList'])
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Forms\Components\Repeater::make('options')
                    ->label('Konfigurasi Pilihan Jawaban')
                    ->schema([
                        Forms\Components\Section::make(fn (\Filament\Forms\Get $get) => $get('../../answer_format') === 'matching' ? 'Sisi Kiri' : 'Opsi Jawaban')
                            ->schema([
                                Forms\Components\TextInput::make('teks_pilihan')
                                    ->label(fn (\Filament\Forms\Get $get) => $get('../../answer_format') === 'complex_fill' ? 'Kata Jawaban (Sesuai Urutan)' : 'Teks Opsi'),
                                
                                Forms\Components\FileUpload::make('image_pilihan')
                                    ->image()->optimize('webp')
                                    ->disk('modul_rahasia')->directory('modul_private/opsi_jawaban')
                                    ->label('Gambar Opsi (Opsional)')
                                    ->visible(fn (\Filament\Forms\Get $get) => $get('../../answer_format') !== 'complex_fill'),
                            ])->columns(2),

                        Forms\Components\Section::make('Sisi Kanan (Pasangannya)')
                            ->schema([
                                Forms\Components\TextInput::make('matching_right')->label('Teks Pasangan'),
                                Forms\Components\FileUpload::make('image_matching_right')
                                    ->image()->optimize('webp')
                                    ->disk('modul_rahasia')->directory('modul_private/opsi_jawaban')
                                    ->label('Gambar Pasangan (Opsional)'),
                            ])
                            ->columns(2)
                            ->visible(fn (\Filament\Forms\Get $get) => $get('../../answer_format') === 'matching'),

                        Forms\Components\Checkbox::make('is_correct')
                            ->label('⭐ Ini Adalah Jawaban Benar')
                            ->visible(fn (\Filament\Forms\Get $get) => $get('../../answer_format') === 'multiple_choice'),
                    ])
                    ->cloneable()->reorderableWithButtons() 
                    ->visible(fn (\Filament\Forms\Get $get) => in_array($get('answer_format'), ['multiple_choice', 'matching', 'complex_fill'])),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('activity.module.title')
                    ->label('Modul')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('activity.title')
                    ->label('Aktivitas')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('answer_format')
                    ->label('Tipe Soal')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}