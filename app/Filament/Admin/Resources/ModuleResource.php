<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ModuleResource\Pages;
use App\Models\Module;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Manajemen Modul';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ==========================================
                // LEVEL 0: INFORMASI MODUL UTAMA
                // ==========================================
                Forms\Components\Section::make('Informasi Modul')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->label('Judul Modul (Misal: Modul Minggu ke-8)'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi Singkat (Opsional)'),
                        
                        Forms\Components\TextInput::make('access_pin')
                            ->label('PIN Akses Modul (Opsional)')
                            ->placeholder('Biarkan kosong jika tidak pakai PIN')
                            ->maxLength(6)
                            ->numeric(),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Modul Aktif?'),
                    ]),

                // ==========================================
                // LEVEL 1: REPEATER AKTIVITAS
                // ==========================================
                Forms\Components\Repeater::make('activities')
                    ->relationship('activities')
                    ->label('Daftar Aktivitas')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->label('Judul Aktivitas (Misal: Aktivitas 1: Mengamati Benda)'),

                        Forms\Components\Section::make('Metrik Penilaian (Untuk Dashboard Guru)')
                            ->description('Centang kemampuan yang diukur pada aktivitas ini sesuai matriks kurikulum.')
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\CheckboxList::make('assessment_metrics.numerasi')
                                        ->label('Kemampuan Numerasi')
                                        ->options([
                                            'RL' => 'RL (Representasi Logis)',
                                            'ML' => 'ML (Matematika Logis)',
                                            'SL' => 'SL',
                                            'PS' => 'PS',
                                            'CiT' => 'CiT',
                                            'DM' => 'DM',
                                            'MP' => 'MP',
                                            'CeT' => 'CeT',
                                        ])
                                        ->columns(2),

                                    Forms\Components\CheckboxList::make('assessment_metrics.fase')
                                        ->label('Fase')
                                        ->options([
                                            'Kon' => 'Kon (Konkret)',
                                            'Pik' => 'Pik (Piktorial)',
                                            'Abs' => 'Abs (Abstrak)',
                                        ]),

                                    Forms\Components\CheckboxList::make('assessment_metrics.steam')
                                        ->label('STEAM')
                                        ->options([
                                            'S' => 'Science',
                                            'T' => 'Technology',
                                            'E' => 'Engineering',
                                            'A' => 'Art',
                                            'M' => 'Mathematics',
                                        ]),
                                ])
                            ])
                            ->collapsible()
                            ->collapsed(true),

                        // ==========================================
                        // GERBONG TAHAPAN (MATERI SEBELUM SOAL)
                        // ==========================================
                        Forms\Components\Section::make('Materi Pembelajaran (Gerbong Tahapan)')
                            ->description('Tambahkan tahapan materi (Mengamati, Diskusi, dll) sebelum murid masuk ke soal Ayo Berlatih.')
                            ->schema([
                                Forms\Components\Repeater::make('stages')
                                    ->label('Daftar Tahapan')
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
                                            ])
                                            ->required(),
                                            
                                        TinyEditor::make('konten_tahapan')
                                            ->label('Isi Materi (Teks/Gambar)')
                                            ->fileAttachmentsDisk('modul_rahasia') // UBAH KE LOCAL (Private)
                                            ->fileAttachmentsVisibility('private') // Set visibility ke private
                                            ->fileAttachmentsDirectory(function (Forms\Get $get) {
                                                // Mengintip judul Modul (naik 3 level) dan judul Aktivitas (naik 2 level)
                                                $modul = Str::slug($get('../../../title') ?? 'modul-baru');
                                                $aktivitas = Str::slug($get('../../title') ?? 'aktivitas-baru');
                                                return "modul_private/{$modul}/{$aktivitas}/tahapan";
                                            })
                                            ->profile('default')
                                            // ->id(fn () => 'tiny-' . Str::random(5))
                                            ->direction('auto')
                                            ->required(),
                                    ])
                                    ->cloneable()
                                    ->collapsible()
                                    ->reorderableWithButtons()
                                    ->collapsed(false)
                                    ->itemLabel(fn (array $state): ?string => $state['tipe_tahapan'] ?? 'Tahapan Baru'),
                            ]),

                        // ==========================================
                        // LEVEL 2: REPEATER SOAL (AYO BERLATIH)
                        // ==========================================
                        Forms\Components\Repeater::make('questions')
                            ->relationship('questions')
                            ->label('Rentetan Soal (Ayo Berlatih)')
                            ->schema([
                                // PENGATURAN TIPE & LAYOUT
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\Select::make('answer_format')
                                        ->options([
                                            'multiple_choice'       => 'Pilihan Ganda / Ceklis',
                                            'number_input'          => 'Input Angka Tunggal',
                                            'text_input'            => 'Input Teks Singkat',
                                            'matching'              => 'Tarik Garis / Menjodohkan',
                                            'true_false_correction' => 'Benar/Salah + Teks Perbaikan',
                                            'complex_fill'          => 'Isian Rumpang (Banyak Titik-titik)',
                                        ])
                                        ->required()
                                        ->live()
                                        ->label('Tipe Jawaban'),

                                    Forms\Components\Select::make('layout_position')
                                        ->options([
                                            'image_left'   => 'Gambar di Kiri, Soal di Kanan',
                                            'image_right'  => 'Soal di Kiri, Gambar di Kanan',
                                            'image_top'    => 'Gambar di Atas, Soal di Bawah',
                                            'image_bottom' => 'Soal di Atas, Gambar di Bawah',
                                        ])
                                        ->default('image_left')
                                        ->required()
                                        ->label('Posisi Gambar Spesifik Soal'),
                                ]),

                                // KONTEN SOAL
                                Forms\Components\Section::make('Konten Pertanyaan')
                                    ->schema([
                                        Forms\Components\FileUpload::make('image')
                                            ->image()
                                            ->disk('modul_rahasia') // UBAH KE LOCAL (Private)
                                            ->visibility('private')
                                            ->directory(function (Forms\Get $get) {
                                                // Mengintip judul Modul (naik 3 level) dan judul Aktivitas (naik 2 level)
                                                $modul = Str::slug($get('../../../title') ?? 'modul-baru');
                                                $aktivitas = Str::slug($get('../../title') ?? 'aktivitas-baru');
                                                return "modul_private/{$modul}/{$aktivitas}/soal";
                                            })
                                            ->label('Gambar Khusus Soal Ini (Bila Ada)'),

                                        Forms\Components\RichEditor::make('question_text')
                                            ->required()
                                            ->label('Teks Pertanyaan')
                                            ->toolbarButtons(['bold', 'italic', 'underline', 'h3', 'bulletList']),
                                    ])->columns(2),
                                
                                Forms\Components\Section::make('Kunci Jawaban & Pembahasan')
                                    ->schema([
                                        Forms\Components\TextInput::make('correct_answer')
                                            ->label('Kunci Jawaban Pasti')
                                            ->placeholder('Misal: 32 (untuk angka) atau "3 puluhan" (untuk teks)')
                                            ->visible(fn (\Filament\Forms\Get $get) => in_array($get('answer_format'), ['number_input', 'text_input']))
                                            ->required(fn (\Filament\Forms\Get $get) => in_array($get('answer_format'), ['number_input', 'text_input'])),

                                        Forms\Components\Placeholder::make('pg_notice')
                                            ->label('Info Kunci Jawaban')
                                            ->content('Untuk soal Pilihan Ganda / Menjodohkan, silakan centang kotak "Jawaban Benar?" pada opsi di bawah.')
                                            ->visible(fn (\Filament\Forms\Get $get) => in_array($get('answer_format'), ['multiple_choice', 'matching', 'true_false_correction'])),

                                        Forms\Components\Textarea::make('answer_explanation')
                                            ->label('Catatan Pembahasan (Opsional)')
                                            ->placeholder('Penjelasan kenapa jawaban ini benar, akan muncul setelah murid selesai ujian.')
                                            ->rows(2),
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),

                                // OPSI JAWABAN
                                Forms\Components\Repeater::make('options')
                                    ->label('Konfigurasi Jawaban & Opsi')
                                    ->schema([
                                        Forms\Components\TextInput::make('teks_pilihan')
                                            ->required()
                                            ->label(fn (\Filament\Forms\Get $get) => $get('../../answer_format') === 'matching' ? 'Teks Sisi Kiri (Misal: 5 Puluhan)' : 'Teks Opsi / Jawaban'),

                                        Forms\Components\TextInput::make('matching_right')
                                            ->label('Teks Sisi Kanan (Pasangannya)')
                                            ->visible(fn (\Filament\Forms\Get $get) => $get('../../answer_format') === 'matching')
                                            ->required(fn (\Filament\Forms\Get $get) => $get('../../answer_format') === 'matching'),

                                        Forms\Components\Checkbox::make('is_correct')
                                            ->label('Ini Jawaban Benar?')
                                            ->visible(fn (\Filament\Forms\Get $get) => $get('../../answer_format') === 'multiple_choice'),
                                    ])
                                    ->columns(2)
                                    ->cloneable()
                                    ->visible(fn (\Filament\Forms\Get $get) => in_array($get('answer_format'), ['multiple_choice', 'matching', 'true_false_correction', 'complex_fill'])),
                            ])
                            ->itemLabel(fn (array $state): ?string => strip_tags($state['question_text'] ?? 'Soal Baru'))
                            ->collapsible()
                            ->collapsed()
                            ->cloneable()
                            ->reorderable()
                            ->columnSpanFull(),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Aktivitas Baru')
                    ->collapsible()
                    ->cloneable()
                    ->reorderable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // ==========================================
                // TOMBOL EXPORT ZIP (JSON + GAMBAR)
                // ==========================================
                Tables\Actions\Action::make('export_zip')
                    ->label('Export ZIP')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('success')
                    ->action(function (\App\Models\Module $record) {
                        $moduleData = $record->load(['activities.questions'])->toArray();
                        $jsonContent = json_encode($moduleData, JSON_PRETTY_PRINT);

                        $zipFileName = 'Backup_Modul_' . $record->id . '_' . date('Ymd_His') . '.zip';
                        $zipPath = storage_path('app/' . $zipFileName);
                        $zip = new \ZipArchive();

                        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                            $zip->addFromString('database_modul.json', $jsonContent);
                            $filesToZip = [];

                            $extractHtmlImages = function($html) use (&$filesToZip) {
                                if (!$html) return;
                                preg_match_all('/src=".*?\/storage\/([^"]+)"/', $html, $matches);
                                if (!empty($matches[1])) {
                                    foreach ($matches[1] as $img) {
                                        $filesToZip[] = urldecode($img);
                                    }
                                }
                            };

                            foreach ($record->activities as $activity) {
                                if (isset($activity->image) && $activity->image) $filesToZip[] = $activity->image;
                                if (isset($activity->sign_language_video) && $activity->sign_language_video) $filesToZip[] = $activity->sign_language_video;
                                
                                // Ekstrak gambar dari deskripsi (jika masih ada data lama)
                                if (isset($activity->description)) {
                                    $extractHtmlImages($activity->description);
                                }

                                // Ekstrak gambar dari gerbong tahapan baru
                                if (isset($activity->stages) && is_array($activity->stages)) {
                                    foreach ($activity->stages as $stage) {
                                        if (isset($stage['konten_tahapan'])) {
                                            $extractHtmlImages($stage['konten_tahapan']);
                                        }
                                    }
                                }

                                foreach ($activity->questions as $question) {
                                    if (isset($question->image) && $question->image) $filesToZip[] = $question->image;
                                    if (isset($question->sign_language_video) && $question->sign_language_video) $filesToZip[] = $question->sign_language_video;
                                    
                                    if (isset($question->question_text)) {
                                        $extractHtmlImages($question->question_text);
                                    }
                                }
                            }

                            $filesToZip = array_unique($filesToZip); 
                            
                            foreach ($filesToZip as $filePath) {
                                $fullPath = storage_path('app/public/' . $filePath);
                                if (File::exists($fullPath)) {
                                    $zip->addFile($fullPath, 'images/' . $filePath); 
                                }
                            }

                            $zip->close();
                            return response()->download($zipPath)->deleteFileAfterSend(true);
                        }
                    }),
                    
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModules::route('/'),
            'create' => Pages\CreateModule::route('/create'),
            'edit' => Pages\EditModule::route('/{record}/edit'),
        ];
    }
}