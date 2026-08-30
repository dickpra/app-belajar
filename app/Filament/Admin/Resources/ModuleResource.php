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

                        Forms\Components\Toggle::make('is_adaptive')
                            ->label('🤖 Aktifkan Mode Pembelajaran Adaptif?')
                            ->helperText('Jika aktif, sistem akan menyeleksi tingkat kesulitan soal secara otomatis berdasarkan kepintaran murid.'),
                            
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
                                            ->fileAttachmentsDisk('modul_rahasia') 
                                            ->fileAttachmentsVisibility('private') 
                                            ->fileAttachmentsDirectory(function (Forms\Get $get) {
                                                $modul = Str::slug($get('../../../title') ?? 'modul-baru');
                                                $aktivitas = Str::slug($get('../../title') ?? 'aktivitas-baru');
                                                return "modul_private/{$modul}/{$aktivitas}/tahapan";
                                            })
                                            ->profile('default')
                                            ->direction('auto')
                                            ->required(),

                                        // 👇 TAMBAHKAN KODE INI DI SINI 👇
                                       Forms\Components\FileUpload::make('sign_language_video')
                                            ->label('🤟 Video Bahasa Isyarat (Opsional)')
                                            ->disk('modul_rahasia') // Tetap pakai disk private agar aman
                                            ->visibility('private')
                                            ->directory(function (Forms\Get $get) {
                                                $modul = Str::slug($get('../../../title') ?? 'modul-baru');
                                                $aktivitas = Str::slug($get('../../title') ?? 'aktivitas-baru');
                                                return "modul_private/{$modul}/{$aktivitas}/video_materi";
                                            })
                                            ->acceptedFileTypes(['video/mp4', 'video/webm']) // Hanya terima video
                                            ->maxSize(51200), // Maksimal 50MB agar server tidak meledak
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
                                            ->directory(function (Forms\Get $get) {
                                                $modul = \Illuminate\Support\Str::slug($get('../../../title') ?? 'modul-baru');
                                                $aktivitas = \Illuminate\Support\Str::slug($get('../../title') ?? 'aktivitas-baru');
                                                return "modul_private/{$modul}/{$aktivitas}/soal_thumbnail";
                                            })
                                            ->label('Gambar Utama Soal (Opsional)'),

                                // 👇 TAMBAHKAN KODE INI DI SINI 👇
                                        Forms\Components\FileUpload::make('sign_language_video')
                                            ->label('🤟 Video Bahasa Isyarat Soal (Opsional)')
                                            ->disk('modul_rahasia')
                                            ->visibility('private')
                                            ->directory(function (Forms\Get $get) {
                                                $modul = \Illuminate\Support\Str::slug($get('../../../title') ?? 'modul-baru');
                                                $aktivitas = \Illuminate\Support\Str::slug($get('../../title') ?? 'aktivitas-baru');
                                                return "modul_private/{$modul}/{$aktivitas}/video_soal";
                                            })
                                            ->acceptedFileTypes(['video/mp4', 'video/webm'])
                                            ->maxSize(51200)
                                            ->previewable(),

                                        // KEMBALI MENGGUNAKAN RICH EDITOR BAWAAN FILAMENT
                                        Forms\Components\RichEditor::make('question_text')
                                            ->label('Teks Pertanyaan (Bisa sisip banyak gambar)')
                                            ->fileAttachmentsDisk('modul_rahasia')
                                            ->fileAttachmentsVisibility('private')
                                            ->fileAttachmentsDirectory(function (Forms\Get $get) {
                                                $modul = \Illuminate\Support\Str::slug($get('../../../title') ?? 'modul-baru');
                                                $aktivitas = \Illuminate\Support\Str::slug($get('../../title') ?? 'aktivitas-baru');
                                                return "modul_private/{$modul}/{$aktivitas}/soal_sisipan";
                                            })
                                            ->toolbarButtons(['bold', 'italic', 'underline', 'strike', 'link', 'h3', 'bulletList', 'orderedList', 'attachFiles'])
                                            ->required(),
                                    ])->columns(1),
                                
                                // ==========================================
                                // AREA KUNCI JAWABAN (SUPER DINAMIS & PINTAR)
                                // ==========================================
                                Forms\Components\Section::make('Kunci Jawaban & Pembahasan')
                                    ->schema([
                                        
                                        // A. KUNCI JAWABAN PASTI (Hanya untuk Teks & Angka)
                                        Forms\Components\TextInput::make('correct_answer')
                                            ->label('Kunci Jawaban Pasti')
                                            ->placeholder('Contoh: 25 (untuk angka) atau "Matahari" (untuk teks)')
                                            ->visible(fn (\Filament\Forms\Get $get) => in_array($get('answer_format'), ['number_input', 'text_input']))
                                            ->required(fn (\Filament\Forms\Get $get) => in_array($get('answer_format'), ['number_input', 'text_input'])),

                                        // B. KUNCI JAWABAN BENAR/SALAH (Khusus true_false_correction)
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
                                            ->label('Teks Perbaikan (Wajib diisi jika kuncinya "SALAH")')
                                            ->placeholder('Misal: Yang benar adalah 3 puluhan 8 satuan')
                                            ->visible(fn (\Filament\Forms\Get $get) => $get('answer_format') === 'true_false_correction' && $get('true_false_answer') === 'Salah')
                                            ->required(fn (\Filament\Forms\Get $get) => $get('answer_format') === 'true_false_correction' && $get('true_false_answer') === 'Salah'),

                                        // C. PETUNJUK DINAMIS UNTUK SOAL OPSI (PG, Menjodohkan, Isian Rumpang)
                                        Forms\Components\Placeholder::make('pg_notice')
                                            ->label('Cara Menentukan Kunci Jawaban')
                                            ->content(function (\Filament\Forms\Get $get) {
                                                return match ($get('answer_format')) {
                                                    'multiple_choice' => '👇 Tambahkan opsi di bawah, lalu centang kotak "⭐ Ini Jawaban Benar". (Boleh centang lebih dari 1 untuk Pilihan Ganda Kompleks).',
                                                    'matching' => '👇 Tuliskan pasangan yang BENAR secara sejajar di kolom Kiri dan Kanan pada bagian opsi di bawah. Tidak perlu dicentang. Sistem akan mengacak posisi kanan otomatis.',
                                                    'complex_fill' => '👇 Tambahkan daftar kata jawaban yang benar secara BERURUTAN dari atas ke bawah di bagian opsi. Tidak perlu dicentang.',
                                                    default => 'Pilih tipe jawaban terlebih dahulu di atas.',
                                                };
                                            })
                                            ->visible(fn (\Filament\Forms\Get $get) => in_array($get('answer_format'), ['multiple_choice', 'matching', 'complex_fill'])),

                                        // D. KOTAK PEMBAHASAN
                                        Forms\Components\RichEditor::make('answer_explanation')
                                            ->label('Catatan Pembahasan (Opsional)')
                                            ->placeholder('Tuliskan penjelasan mengapa jawaban ini benar. Penjelasan ini bisa ditampilkan ke murid setelah evaluasi.')
                                            ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList'])
                                            ->columnSpanFull(),
                                            
                                    ])
                                    ->columns(1)
                                    ->collapsible()
                                    ->collapsed(false),

                                // ==========================================
                                // UPGRADE OPSI JAWABAN (OTOMATIS HILANG JIKA TIDAK BUTUH OPSI)
                                // ==========================================
                                Forms\Components\Repeater::make('options')
                                    ->label('Konfigurasi Pilihan Jawaban')
                                    ->schema([
                                        Forms\Components\Section::make(fn (\Filament\Forms\Get $get) => $get('../../answer_format') === 'matching' ? 'Sisi Kiri' : 'Opsi Jawaban')
                                            ->schema([
                                                Forms\Components\TextInput::make('teks_pilihan')
                                                    ->label(fn (\Filament\Forms\Get $get) => $get('../../answer_format') === 'complex_fill' ? 'Kata Jawaban (Sesuai Urutan)' : 'Teks Opsi (Kosongkan jika hanya gambar)'),
                                                
                                                Forms\Components\FileUpload::make('image_pilihan')
                                                    ->image()
                                                    ->optimize('webp')
                                                    ->imageEditor()
                                                    ->disk('modul_rahasia')
                                                    ->directory('modul_private/opsi_jawaban')
                                                    ->label('Gambar Opsi (Opsional)')
                                                    ->visible(fn (\Filament\Forms\Get $get) => $get('../../answer_format') !== 'complex_fill'), // Disembunyikan untuk isian rumpang
                                            ])->columns(2),

                                        Forms\Components\Section::make('Sisi Kanan (Pasangannya)')
                                            ->schema([
                                                Forms\Components\TextInput::make('matching_right')
                                                    ->label('Teks Pasangan (Kosongkan jika hanya gambar)'),
                                                
                                                Forms\Components\FileUpload::make('image_matching_right')
                                                    ->image()
                                                    ->optimize('webp')
                                                    ->imageEditor()
                                                    ->disk('modul_rahasia')
                                                    ->directory('modul_private/opsi_jawaban')
                                                    ->label('Gambar Pasangan (Opsional)'),
                                            ])
                                            ->columns(2)
                                            ->visible(fn (\Filament\Forms\Get $get) => $get('../../answer_format') === 'matching'),

                                        Forms\Components\Checkbox::make('is_correct')
                                            ->label('⭐ Ini Adalah Jawaban Benar')
                                            ->visible(fn (\Filament\Forms\Get $get) => $get('../../answer_format') === 'multiple_choice'),
                                    ])
                                    ->cloneable()
                                    ->reorderableWithButtons() 
                                    // REPEATER OPSI AKAN HILANG TOTAL JIKA ADMIN MEMILIH BENAR/SALAH, ANGKA, ATAU TEKS SINGKAT!
                                    ->visible(fn (\Filament\Forms\Get $get) => in_array($get('answer_format'), ['multiple_choice', 'matching', 'complex_fill'])),
                            ])
                            ->itemLabel(function (array $state): ?string {
                                $content = $state['question_text'] ?? null;
                                return is_string($content) ? \Illuminate\Support\Str::limit(strip_tags($content), 40) : 'Soal Baru';
                            })
                            ->collapsible()
                            ->collapsed()
                            ->cloneable()
                            ->reorderableWithButtons()
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
                                
                                if (isset($activity->description)) {
                                    $extractHtmlImages($activity->description);
                                }

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