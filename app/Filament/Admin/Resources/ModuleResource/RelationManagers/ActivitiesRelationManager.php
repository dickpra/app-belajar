<?php

namespace App\Filament\Admin\Resources\ModuleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Illuminate\Support\Str;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';
    protected static ?string $title = 'Manajemen Aktivitas & Evaluasi';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Manajemen Aktivitas')
                ->tabs([
                    
                    // ==========================================
                    // TAB 1: INFORMASI & METRIK
                    // ==========================================
                    Forms\Components\Tabs\Tab::make('1. Identitas & Metrik')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->required()
                                ->label('Judul Aktivitas (Misal: Aktivitas 1: Mengamati Benda)'),

                            Forms\Components\Section::make('Metrik Penilaian (Dashboard Guru)')
                                ->description('Centang kemampuan yang diukur pada aktivitas ini sesuai matriks kurikulum.')
                                ->schema([
                                    Forms\Components\Grid::make(3)->schema([
                                        Forms\Components\CheckboxList::make('assessment_metrics.numerasi')
                                            ->label('Kemampuan Numerasi')
                                            ->options([
                                                'RL' => 'RL (Representasi Logis)',
                                                'ML' => 'ML (Matematika Logis)',
                                                'SL' => 'SL', 'PS' => 'PS', 'CiT' => 'CiT', 'DM' => 'DM', 'MP' => 'MP', 'CeT' => 'CeT',
                                            ])->columns(2),

                                        Forms\Components\CheckboxList::make('assessment_metrics.fase')
                                            ->label('Fase')
                                            ->options(['Kon' => 'Konkret', 'Pik' => 'Piktorial', 'Abs' => 'Abstrak']),

                                        Forms\Components\CheckboxList::make('assessment_metrics.steam')
                                            ->label('STEAM')
                                            ->options(['S' => 'Science', 'T' => 'Technology', 'E' => 'Engineering', 'A' => 'Art', 'M' => 'Mathematics']),
                                    ])
                                ]),
                        ]),

                    // ==========================================
                    // TAB 2: GERBONG TAHAPAN (MATERI)
                    // ==========================================
                    Forms\Components\Tabs\Tab::make('2. Tahapan Pembelajaran')
                        ->icon('heroicon-o-book-open')
                        ->schema([
                            Forms\Components\Repeater::make('stages')
                                ->label('Materi Sebelum Soal')
                                ->schema([
                                    Forms\Components\Select::make('tipe_tahapan')
                                        ->label('Ikon & Judul Tahapan')
                                        ->options([
                                            'berpikir'  => '🤔 Berpikir / Pemantik',
                                            'amati'     => '🔍 Ayo Mengamati',
                                            'mencoba'   => '🧪 Mari Mencoba',
                                            'diskusi'   => '💬 Ruang Diskusi',
                                            'simpulkan' => '💡 Mari Menyimpulkan',
                                            'materi'    => '📖 Bacaan Materi',
                                        ])->required(),

                                    Forms\Components\FileUpload::make('sign_language_video')
                                        ->label('🤟 Video Bahasa Isyarat (Opsional)')
                                        ->disk('modul_rahasia')->visibility('private')
                                        ->directory(function (RelationManager $livewire, Forms\Get $get) {
                                            $modul = Str::slug($livewire->getOwnerRecord()->title ?? 'modul');
                                            $aktivitas = Str::slug($get('../../title') ?? 'aktivitas');
                                            return "modul_private/{$modul}/{$aktivitas}/video_materi";
                                        })
                                        ->acceptedFileTypes(['video/mp4', 'video/webm'])
                                        ->maxSize(10240),
                                        
                                    Forms\Components\RichEditor::make('konten_tahapan')
                                        ->label('Isi Materi (Teks & Gambar)')
                                        ->fileAttachmentsDisk('modul_rahasia')
                                        ->fileAttachmentsVisibility('private')
                                        ->fileAttachmentsDirectory(function (RelationManager $livewire, Forms\Get $get) {
                                            $modul = \Illuminate\Support\Str::slug($livewire->getOwnerRecord()->title ?? 'modul');
                                            $aktivitas = \Illuminate\Support\Str::slug($get('../../title') ?? 'aktivitas');
                                            return "modul_private/{$modul}/{$aktivitas}/tahapan";
                                        })

                                        ->required(),
                                ])
                                ->cloneable()->collapsible()->reorderableWithButtons()
                                ->itemLabel(fn (array $state): ?string => $state['tipe_tahapan'] ?? 'Tahapan Baru'),
                        ]),

                    // ==========================================
                    // TAB 3: SOAL & KUNCI JAWABAN
                    // ==========================================
                    Forms\Components\Tabs\Tab::make('3. Soal & Evaluasi')
                        ->icon('heroicon-o-pencil-square')
                        ->schema([
                            Forms\Components\Repeater::make('questions')
                                ->relationship('questions')
                                ->label('Rentetan Soal (Ayo Berlatih)')
                                ->schema([
                                    Forms\Components\Grid::make(3)->schema([
                                        Forms\Components\Select::make('answer_format')
                                            ->options([
                                                'multiple_choice'       => 'Pilihan Ganda (Teks/Gambar)',
                                                'number_input'          => 'Input Angka Tunggal',
                                                'text_input'            => 'Input Teks Singkat',
                                                'matching'              => 'Menjodohkan (Kiri & Kanan)',
                                                'true_false_correction' => 'Benar/Salah + Teks Perbaikan',
                                                'complex_fill'          => 'Isian Rumpang (Banyak Titik)',
                                            ])->required()->live()->label('Tipe Jawaban'),

                                        Forms\Components\Select::make('layout_position')
                                            ->options([
                                                'image_left'   => 'Gambar di Kiri',
                                                'image_right'  => 'Gambar di Kanan',
                                                'image_top'    => 'Gambar di Atas',
                                                'image_bottom' => 'Gambar di Bawah',
                                            ])->default('image_top')->required()->label('Posisi Gambar'),
                                        
                                        Forms\Components\Select::make('difficulty')
                                            ->options(['easy'=>'🌟 Mudah', 'medium'=>'⭐⭐ Sedang', 'hard'=>'🔥 Sulit (HOTS)'])
                                            ->default('medium')->required()->label('Tingkat Kesulitan'),
                                    ]),

                                    Forms\Components\Section::make('Konten Pertanyaan')
                                        ->schema([
                                            Forms\Components\Grid::make(2)->schema([
                                                Forms\Components\FileUpload::make('image')
                                                    ->image()->optimize('webp')->imageEditor()
                                                    ->disk('modul_rahasia')->visibility('private')
                                                    ->directory(function (RelationManager $livewire, Forms\Get $get) {
                                                        $modul = Str::slug($livewire->getOwnerRecord()->title ?? 'modul');
                                                        $aktivitas = Str::slug($get('../../title') ?? 'aktivitas');
                                                        return "modul_private/{$modul}/{$aktivitas}/soal_thumbnail";
                                                    })->label('Gambar Utama Soal'),

                                                Forms\Components\FileUpload::make('sign_language_video')
                                                    ->label('🤟 Video Isyarat Soal')
                                                    ->disk('modul_rahasia_video')->visibility('private')
                                                    ->directory(function (RelationManager $livewire, Forms\Get $get) {
                                                        $modul = Str::slug($livewire->getOwnerRecord()->title ?? 'modul');
                                                        $aktivitas = Str::slug($get('../../title') ?? 'aktivitas');
                                                        return "modul_private/{$modul}/{$aktivitas}/video_soal";
                                                    })
                                                    ->acceptedFileTypes(['video/mp4', 'video/webm'])->maxSize(10240),
                                            ]),

                                            Forms\Components\RichEditor::make('question_text')
                                                ->label('Teks Pertanyaan (Bisa sisip gambar)')
                                                ->fileAttachmentsDisk('modul_rahasia')->fileAttachmentsVisibility('private')
                                                ->fileAttachmentsDirectory(function (RelationManager $livewire, Forms\Get $get) {
                                                    $modul = Str::slug($livewire->getOwnerRecord()->title ?? 'modul');
                                                    $aktivitas = Str::slug($get('../../title') ?? 'aktivitas');
                                                    return "modul_private/{$modul}/{$aktivitas}/soal_sisipan";
                                                })
                                                ->toolbarButtons(['bold', 'italic', 'underline', 'strike', 'link', 'h3', 'bulletList', 'orderedList', 'attachFiles'])
                                                ->required(),
                                        ]),
                                    
                                    // KUNCI JAWABAN
                                    Forms\Components\Section::make('Kunci Jawaban & Pembahasan')
                                        ->schema([
                                            Forms\Components\TextInput::make('correct_answer')
                                                ->label('Kunci Jawaban Pasti')
                                                ->placeholder('Contoh: 25')
                                                ->visible(fn (Forms\Get $get) => in_array($get('answer_format'), ['number_input', 'text_input']))
                                                ->required(fn (Forms\Get $get) => in_array($get('answer_format'), ['number_input', 'text_input'])),

                                            Forms\Components\Select::make('true_false_answer')
                                                ->label('Kunci Jawaban yang Tepat')
                                                ->options(['Benar'=>'Pernyataan BENAR', 'Salah'=>'Pernyataan SALAH'])
                                                ->visible(fn (Forms\Get $get) => $get('answer_format') === 'true_false_correction')
                                                ->required(fn (Forms\Get $get) => $get('answer_format') === 'true_false_correction')
                                                ->live(),

                                            Forms\Components\TextInput::make('correction_text')
                                                ->label('Teks Perbaikan (Jika Salah)')
                                                ->visible(fn (Forms\Get $get) => $get('answer_format') === 'true_false_correction' && $get('true_false_answer') === 'Salah')
                                                ->required(fn (Forms\Get $get) => $get('answer_format') === 'true_false_correction' && $get('true_false_answer') === 'Salah'),

                                            Forms\Components\Placeholder::make('pg_notice')
                                                ->label('Panduan Opsi')
                                                ->content(fn (Forms\Get $get) => match ($get('answer_format')) {
                                                    'multiple_choice' => 'Tambahkan opsi di bawah, centang kotak "⭐ Jawaban Benar".',
                                                    'matching' => 'Tulis pasangan BENAR sejajar di Kiri & Kanan. Sistem akan mengacak Kanan.',
                                                    'complex_fill' => 'Tambahkan kata berurutan untuk jawaban isian rumpang.',
                                                    default => 'Pilih tipe jawaban di atas.',
                                                })
                                                ->visible(fn (Forms\Get $get) => in_array($get('answer_format'), ['multiple_choice', 'matching', 'complex_fill'])),

                                            Forms\Components\RichEditor::make('answer_explanation')
                                                ->label('Pembahasan Jawaban (Opsional)')
                                                ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList']),
                                        ])->collapsible()->collapsed(false),

                                    // OPSI JAWABAN (REPEATER DINAMIS)
                                    Forms\Components\Repeater::make('options')
                                        ->label('Konfigurasi Opsi')
                                        ->schema([
                                            Forms\Components\Section::make(fn (Forms\Get $get) => $get('../../answer_format') === 'matching' ? 'Sisi Kiri' : 'Opsi')
                                                ->schema([
                                                    Forms\Components\TextInput::make('teks_pilihan')
                                                        ->label(fn (Forms\Get $get) => $get('../../answer_format') === 'complex_fill' ? 'Kata Jawaban' : 'Teks Opsi'),
                                                    Forms\Components\FileUpload::make('image_pilihan')
                                                        ->image()->optimize('webp')->imageEditor()
                                                        ->disk('modul_rahasia')->directory('modul_private/opsi_jawaban')->label('Gambar Opsi')
                                                        ->visible(fn (Forms\Get $get) => $get('../../answer_format') !== 'complex_fill'),
                                                ])->columns(2),

                                            Forms\Components\Section::make('Sisi Kanan (Pasangan)')
                                                ->schema([
                                                    Forms\Components\TextInput::make('matching_right')->label('Teks Pasangan'),
                                                    Forms\Components\FileUpload::make('image_matching_right')
                                                        ->image()->optimize('webp')->imageEditor()
                                                        ->disk('modul_rahasia')->directory('modul_private/opsi_jawaban')->label('Gambar Pasangan'),
                                                ])->columns(2)->visible(fn (Forms\Get $get) => $get('../../answer_format') === 'matching'),

                                            Forms\Components\Checkbox::make('is_correct')
                                                ->label('⭐ Ini Adalah Jawaban Benar')
                                                ->visible(fn (Forms\Get $get) => $get('../../answer_format') === 'multiple_choice'),
                                        ])
                                        ->cloneable()->reorderableWithButtons()
                                        ->visible(fn (Forms\Get $get) => in_array($get('answer_format'), ['multiple_choice', 'matching', 'complex_fill'])),
                                ])
                                ->cloneable()->collapsible()
                                ->itemLabel(fn (array $state): ?string => is_string($state['question_text'] ?? null) ? Str::limit(strip_tags($state['question_text']), 40) : 'Soal Baru'),
                        ]),
                ])->columnSpanFull()
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Nama Aktivitas'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('+ Tambah Aktivitas')->modalWidth('7xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->modalWidth('7xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}