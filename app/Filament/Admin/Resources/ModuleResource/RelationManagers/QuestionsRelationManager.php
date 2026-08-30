<?php

namespace App\Filament\Admin\Resources\ModuleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';
    protected static ?string $title = 'Manajemen Soal (Ayo Berlatih)';
    protected static ?string $icon = 'heroicon-o-clipboard-document-list';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // 1. PEMILIHAN AKTIVITAS
                Forms\Components\Select::make('activity_id')
                    ->label('📍 Soal ini ditujukan untuk aktivitas mana?')
                    ->options(function ($livewire) {
                        // Otomatis hanya menampilkan aktivitas milik modul yang sedang dibuka!
                        return $livewire->getOwnerRecord()->activities()->pluck('title', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),

                // 2. TABS UI SUPER RAPI
                Forms\Components\Tabs::make('Pengaturan Soal')
                    ->tabs([
                        // TAB 1: PERTANYAAN
                        Forms\Components\Tabs\Tab::make('1. Pertanyaan')
                            ->icon('heroicon-o-chat-bubble-bottom-center-text')
                            ->schema([
                                Forms\Components\Select::make('answer_format')
                                    ->options([
                                        'multiple_choice' => 'Pilihan Ganda',
                                        'number_input' => 'Input Angka Tunggal',
                                        'text_input' => 'Input Teks Singkat',
                                        'matching' => 'Menjodohkan',
                                        'true_false_correction' => 'Benar/Salah + Perbaikan',
                                        'complex_fill' => 'Isian Rumpang',
                                    ])
                                    ->required()->live()->label('Tipe Soal'),

                                Forms\Components\FileUpload::make('image')
                                    ->image()->optimize('webp')->disk('modul_rahasia')->visibility('private')
                                    ->directory('modul_private/soal_thumbnail')->label('Gambar Utama'),

                                Forms\Components\RichEditor::make('question_text')
                                    ->label('Teks Pertanyaan')
                                    ->fileAttachmentsDisk('modul_rahasia')->fileAttachmentsVisibility('private')
                                    ->directory('modul_private/soal_sisipan')
                                    ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'attachFiles'])
                                    ->required(),
                            ]),

                        // TAB 2: JAWABAN & OPSI
                        Forms\Components\Tabs\Tab::make('2. Jawaban & Opsi')
                            ->icon('heroicon-o-check-circle')
                            ->schema([
                                Forms\Components\TextInput::make('correct_answer')
                                    ->label('Kunci Jawaban Pasti')
                                    ->visible(fn (\Filament\Forms\Get $get) => in_array($get('answer_format'), ['number_input', 'text_input']))
                                    ->required(fn (\Filament\Forms\Get $get) => in_array($get('answer_format'), ['number_input', 'text_input'])),

                                Forms\Components\Select::make('true_false_answer')
                                    ->label('Kunci Jawaban Tepat')->options(['Benar' => 'BENAR', 'Salah' => 'SALAH'])
                                    ->visible(fn (\Filament\Forms\Get $get) => $get('answer_format') === 'true_false_correction')->live(),

                                Forms\Components\TextInput::make('correction_text')
                                    ->label('Teks Perbaikan (Wajib jika SALAH)')
                                    ->visible(fn (\Filament\Forms\Get $get) => $get('answer_format') === 'true_false_correction' && $get('true_false_answer') === 'Salah'),

                                Forms\Components\Repeater::make('options')
                                    ->label('Daftar Opsi / Pasangan')
                                    ->schema([
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('teks_pilihan')->label('Teks Kiri/Opsi'),
                                            Forms\Components\FileUpload::make('image_pilihan')->image()->disk('modul_rahasia')->directory('modul_private/opsi')->label('Gambar Opsi')->visible(fn (\Filament\Forms\Get $get) => $get('../../answer_format') !== 'complex_fill'),
                                            
                                            Forms\Components\TextInput::make('matching_right')->label('Teks Kanan (Pasangan)')->visible(fn (\Filament\Forms\Get $get) => $get('../../answer_format') === 'matching'),
                                            Forms\Components\FileUpload::make('image_matching_right')->image()->disk('modul_rahasia')->directory('modul_private/opsi')->label('Gambar Pasangan')->visible(fn (\Filament\Forms\Get $get) => $get('../../answer_format') === 'matching'),
                                        ]),
                                        Forms\Components\Checkbox::make('is_correct')->label('⭐ Ini Jawaban Benar')->visible(fn (\Filament\Forms\Get $get) => $get('../../answer_format') === 'multiple_choice'),
                                    ])
                                    ->cloneable()->reorderableWithButtons()
                                    ->visible(fn (\Filament\Forms\Get $get) => in_array($get('answer_format'), ['multiple_choice', 'matching', 'complex_fill'])),
                            ]),

                        // TAB 3: MEDIA EKSTRA
                        Forms\Components\Tabs\Tab::make('3. Ekstra & Media')
                            ->icon('heroicon-o-video-camera')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\Select::make('layout_position')->options(['image_top'=>'Atas', 'image_bottom'=>'Bawah', 'image_left'=>'Kiri', 'image_right'=>'Kanan'])->default('image_top')->label('Posisi Gambar'),
                                    Forms\Components\Select::make('difficulty')->options(['easy'=>'🌟 Mudah', 'medium'=>'⭐⭐ Sedang', 'hard'=>'🔥 Sulit'])->default('medium')->label('Tingkat Kesulitan'),
                                ]),
                                Forms\Components\FileUpload::make('sign_language_video')
                                    ->label('🤟 Video Isyarat Soal')
                                    ->disk('modul_rahasia')->visibility('private')->directory('modul_private/video_soal')
                                    ->acceptedFileTypes(['video/mp4', 'video/webm'])->maxSize(51200)->previewable(false),
                                Forms\Components\RichEditor::make('answer_explanation')->label('Pembahasan (Opsional)')->toolbarButtons(['bold', 'italic', 'underline']),
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('activity.title')
                    ->label('Masuk di Aktivitas:')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('answer_format')
                    ->label('Tipe Soal')
                    ->badge(),
                Tables\Columns\TextColumn::make('question_text')
                    ->label('Pertanyaan')
                    ->html()
                    ->limit(50),
            ])
            ->headerActions([
                // KEAJAIBAN 1: Tambah Soal Pakai Slide-over Modal!
                Tables\Actions\Action::make('tambah_soal_baru')
                    ->label('Tambah Soal Baru')
                    ->icon('heroicon-o-plus-circle')
                    ->form(fn (Form $form) => $this->form($form))
                    ->action(function (array $data) {
                        \App\Models\Question::create($data);
                    })
                    ->slideOver(), // Bikin UI Melayang dari Kanan!
            ])
            ->actions([
                // KEAJAIBAN 2: Edit Soal Pakai Slide-over Modal!
                Tables\Actions\EditAction::make()
                    ->using(function ($record, array $data) {
                        $record->update($data);
                        return $record;
                    })
                    ->slideOver(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}