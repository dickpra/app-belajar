<?php

namespace App\Filament\Admin\Resources\ModuleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';
    protected static ?string $title = 'Manajemen Aktivitas & Evaluasi';

    // 👇 MESIN PEMBERSIH UTAMA (Dipanggil oleh semua Satpam di bawah) 👇
    protected static function cleanTrixHTML(?string $html): ?string
    {
        if (!$html) return $html;

        // 1. JURUS PAMUNGKAS (NUKE):
        // Cari tag <figcaption> yang di dalamnya mengandung 'attachment__name' atau 'attachment__size'.
        // Jika ketemu, hapus KESELURUHAN <figcaption>-nya sekalian sampai ke akar-akarnya!
        $html = preg_replace('/<figcaption[^>]*>.*?attachment__(name|size).*?<\/figcaption>/is', '', $html);

        // 2. Jaga-jaga jika span nama/size-nya entah bagaimana berada di luar figcaption
        $html = preg_replace('/<[^>]*attachment__(name|size)[^>]*>.*?<\/[a-z0-9]+>/is', '', $html);

        // 3. Bersihkan titik pemisah yang sering nempel " · " atau "&middot;"
        $html = str_replace([' · ', '·', '&middot;'], '', $html);

        // 4. Hapus figcaption yang terlanjur kosong
        $html = preg_replace('/<figcaption[^>]*>(?:\s|&nbsp;|<br\s*\/?>)*<\/figcaption>/is', '', $html);

        return $html;
    }
    // 👆 ========================================================== 👆

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
                                // 👇 SATPAM BEDAH PRESISI (MATERI) 👇
                                ->dehydrateStateUsing(function (?array $state): ?array {
                                    if (!$state) return $state;
                                    foreach ($state as $key => $item) {
                                        if (isset($item['konten_tahapan'])) {
                                            $state[$key]['konten_tahapan'] = self::cleanTrixHTML($item['konten_tahapan']);
                                        }
                                    }
                                    return $state;
                                })
                                // 👆 ================================ 👆
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
                            
                            Forms\Components\Section::make('⚡ MODE LATIHAN INSTAN AKTIF')
                                ->schema([
                                    Forms\Components\Placeholder::make('info_instan')
                                        ->hiddenLabel()
                                        ->content(new HtmlString('
                                            <div style="color: #b45309; font-size: 0.95rem; font-weight: bold;">
                                                Karena Anda mengaktifkan Mode Instan (Ala Duolingo) di pengaturan Modul:
                                                <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem; color: #92400e;">
                                                    <li>Tipe soal "Isian Rumpang" (Complex Fill) <strong>dikunci/dimatikan</strong> karena rentan dinilai salah oleh mesin jika ada typo.</li>
                                                    <li>Kunci Jawaban <strong>WAJIB</strong> diisi agar mesin bisa memberikan nilai di tempat.</li>
                                                </ul>
                                            </div>
                                        ')),
                                ])
                                ->visible(fn (RelationManager $livewire) => $livewire->getOwnerRecord()->is_instant_mode)
                                ->extraAttributes(['style' => 'background-color: #fffbeb; border: 2px solid #f59e0b;']),
                            
                            Forms\Components\Section::make('🤖 Panduan Input Soal Adaptif (AI)')
                                ->schema([
                                    Forms\Components\Placeholder::make('panduan_ai')
                                        ->hiddenLabel()
                                        ->content(new HtmlString('
                                            <div style="background-color: #eff6ff; border-left: 6px solid #3b82f6; padding: 1.25rem; border-radius: 0.5rem; color: #1e3a8a; font-size: 0.9rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                                <h4 style="font-weight: 800; font-size: 1rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                                                    <span style="font-size: 1.25rem;">💡</span> Aturan Wajib Sistem AI Pemilah Soal
                                                </h4>
                                                <p style="margin-bottom: 0.75rem; line-height: 1.5;">Sistem akan memilah dan <strong>hanya menampilkan salah satu tingkat kesulitan</strong> berdasarkan kemampuan murid. Harap ikuti tata cara pengisian di bawah ini:</p>
                                                <ul style="list-style-type: none; padding-left: 0; display: flex; flex-direction: column; gap: 0.75rem;">
                                                    <li style="display: flex; gap: 0.5rem;">
                                                        <span style="font-weight: 900; color: #2563eb;">1.</span>
                                                        <div><strong>Soal Menyesuaikan Kemampuan (Adaptif):</strong> Buatlah 3 soal berurutan untuk 1 topik yang sama. Setel tingkat kesulitannya menjadi <strong>1 Mudah, 1 Sedang, dan 1 Sulit</strong>. Murid hanya akan melihat 1 dari 3 soal ini.</div>
                                                    </li>
                                                    <li style="display: flex; gap: 0.5rem;">
                                                        <span style="font-weight: 900; color: #2563eb;">2.</span>
                                                        <div><strong>Soal Wajib (Pasti Muncul):</strong> Jika ada soal krusial yang WAJIB dijawab semua murid tanpa kecuali, silakan buat soal tersebut, lalu <strong>duplikasikan menjadi 3 buah</strong>. Beri label Mudah pada duplikat pertama, Sedang pada duplikat kedua, dan Sulit pada duplikat ketiga.</div>
                                                    </li>
                                                </ul>
                                            </div>
                                        ')),
                                ])->collapsible()->collapsed(true),

                            Forms\Components\Repeater::make('questions')
                                ->relationship('questions')
                                ->label('Rentetan Soal (Ayo Berlatih)')
                                // 👇 SATPAM BEDAH PRESISI (CREATE SOAL BARU) 👇
                                ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                    foreach (['question_text', 'answer_explanation'] as $field) {
                                        if (isset($data[$field])) {
                                            $data[$field] = self::cleanTrixHTML($data[$field]);
                                        }
                                    }
                                    return $data;
                                })
                                // 👇 SATPAM BEDAH PRESISI (UPDATE SOAL LAMA) 👇
                                ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                    foreach (['question_text', 'answer_explanation'] as $field) {
                                        if (isset($data[$field])) {
                                            $data[$field] = self::cleanTrixHTML($data[$field]);
                                        }
                                    }
                                    return $data;
                                })
                                // 👆 ========================================= 👆
                                ->schema([
                                    Forms\Components\Grid::make(3)->schema([
                                        
                                        Forms\Components\Select::make('answer_format')
                                            ->options(config('soal.tipe')) 
                                            ->disableOptionWhen(fn (string $value, RelationManager $livewire) => 
                                                $livewire->getOwnerRecord()->is_instant_mode && in_array($value, ['complex_fill'])
                                            )
                                            ->required()->live()->label('Tipe Jawaban'),
                                        
                                        Forms\Components\Select::make('difficulty')
                                            ->options(['easy'=>'🌟 Mudah', 'medium'=>'⭐⭐ Sedang', 'hard'=>'🔥 Sulit (HOTS)'])
                                            ->default('easy')->required()->label('Tingkat Kesulitan AI (Wajib Set)'),
                                    ]),

                                    Forms\Components\Section::make('Konten Pertanyaan')
                                        ->schema([
                                            Forms\Components\Grid::make(2)->schema([
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
                                                ->fileAttachmentsDisk('modul_rahasia')
                                                ->fileAttachmentsVisibility('private')
                                                ->fileAttachmentsDirectory(function (RelationManager $livewire, Forms\Get $get) {
                                                    $modul = Str::slug($livewire->getOwnerRecord()->title ?? 'modul');
                                                    $aktivitas = Str::slug($get('../../title') ?? 'aktivitas');
                                                    return "modul_private/{$modul}/{$aktivitas}/soal_sisipan";
                                                })
                                                ->toolbarButtons(['bold', 'italic', 'underline', 'strike', 'link', 'h3', 'bulletList', 'orderedList', 'attachFiles'])
                                                ->required(),
                                        ]),
                                    
                                    Forms\Components\Section::make('Kunci Jawaban & Pembahasan')
                                        ->schema([
                                            
                                            Forms\Components\TextInput::make('correct_answer')
                                                ->label('Kunci Jawaban Pasti')
                                                ->helperText(fn (RelationManager $livewire) => 
                                                    $livewire->getOwnerRecord()->is_instant_mode 
                                                    ? '⚠️ MODE INSTAN: Wajib diisi agar mesin bisa menilai!' 
                                                    : 'Opsional (Hanya untuk referensi).'
                                                )
                                                ->visible(fn (Forms\Get $get) => in_array($get('answer_format'), ['number_input', 'text_input']))
                                                ->required(fn (Forms\Get $get, RelationManager $livewire) => 
                                                    $livewire->getOwnerRecord()->is_instant_mode && in_array($get('answer_format'), ['number_input', 'text_input'])
                                                ),

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