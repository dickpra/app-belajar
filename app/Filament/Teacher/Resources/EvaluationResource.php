<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\EvaluationResource\Pages;
use App\Models\Student;
use App\Models\ActivitySubmission;
use App\Models\StudentAnswer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use App\Services\AutoGrader; 

class EvaluationResource extends Resource
{
    protected static ?string $model = Student::class; 

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Buku Penilaian';
    protected static ?string $pluralModelLabel = 'Daftar Murid & Koreksi';
    protected static ?string $navigationGroup = 'Evaluasi';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('activitySubmissions'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Murid')
                    ->searchable()
                    ->weight('bold')
                    ->size('lg'),

                Tables\Columns\TextColumn::make('pending_count')
                    ->label('Status Koreksi')
                    ->getStateUsing(function ($record) {
                        return ActivitySubmission::where('student_id', $record->id)
                            ->where('status', 'menunggu_koreksi')
                            ->count();
                    })
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "⚠️ $state Butuh Koreksi" : '✅ Tuntas'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(fn ($record) => ActivitySubmission::where('student_id', $record->id)->where('status', 'menunggu_koreksi')->count() > 0 ? 'Buka Koreksi' : 'Lihat Rapot')
                    ->icon('heroicon-o-pencil-square')
                    ->button()
                    ->color(fn ($record) => ActivitySubmission::where('student_id', $record->id)->where('status', 'menunggu_koreksi')->count() > 0 ? 'warning' : 'gray'),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema(function ($record) {
            if (!$record) return [];

            $submissions = \App\Models\ActivitySubmission::with(['activity.module'])
                ->where('student_id', $record->id)
                ->get()
                ->sortBy(function ($submission) {
                    return $submission->activity->module_id . '-' . $submission->activity_id;
                });

            if ($submissions->isEmpty()) {
                return [
                    Forms\Components\Placeholder::make('kosong')
                        ->label('')
                        ->content(new HtmlString('<div class="text-center text-gray-500 py-8">Belum ada tugas yang dikerjakan.</div>'))
                ];
            }

            // ==========================================
            // WIDGET PROFIL & SKOR MURID
            // ==========================================
            // 1. Hitung Statistik Rapor
            $totalMisi = $submissions->count();
            $misiSelesai = $submissions->where('status', 'dinilai')->count();
            $misiPending = $submissions->where('status', 'menunggu_koreksi')->count();
            
            $rataRata = 0;
            if ($misiSelesai > 0) {
                $rataRata = round($submissions->where('status', 'dinilai')->avg('total_score'), 1);
            }
            
            // 2. Tentukan Predikat berdasarkan rata-rata
            $predikat = 'Belajar lebih Giat lagi 📉';
            $warnaPredikat = '#f43f5e'; // Merah
            
            if ($rataRata >= 90) {
                $predikat = 'Sangat Memuaskan 🌟';
                $warnaPredikat = '#10b981'; // Hijau
            } elseif ($rataRata >= 75) {
                $predikat = 'Baik Sekali 📈';
                $warnaPredikat = '#3b82f6'; // Biru
            } elseif ($rataRata >= 60) {
                $predikat = 'Cukup Baik ⚠️';
                $warnaPredikat = '#f59e0b'; // Kuning
            }

            $fields = [];

            // 3. Masukkan Widget ke dalam Array Form
            // 3. Masukkan Widget ke dalam Array Form
            $fields[] = Forms\Components\Placeholder::make('widget_rapor')
                ->hiddenLabel()
                ->content(new HtmlString("
                    <div style='background: white; border: 3px solid #cbd5e1; border-radius: 1.5rem; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1); display: flex; flex-wrap: wrap; gap: 2rem; align-items: center; width: 100%;'>
                        
                        <!-- Header Profil -->
                        <div style='display: flex; align-items: center; gap: 1.25rem; min-width: 250px; flex-shrink: 0;'>
                            <div style='width: 5rem; height: 5rem; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; border: 4px solid white; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);'>👦🏻</div>
                            <div>
                                <h2 style='font-size: 1.75rem; font-weight: 900; color: #1e293b; margin: 0; line-height: 1.2;'>{$record->name}</h2>
                                <span style='font-size: 0.875rem; font-weight: 800; color: #64748b; background: #f1f5f9; padding: 0.375rem 1rem; border-radius: 9999px; display: inline-block; margin-top: 0.5rem; border: 2px solid #e2e8f0;'>ID Murid: {$record->id}</span>
                            </div>
                        </div>

                        <!-- Kartu Statistik -->
                        <div style='display: flex; flex-wrap: wrap; gap: 1rem; flex: 1; justify-content: flex-end;'>
                            
                            <!-- Kartu Rata-Rata Skor -->
                            <div style='background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 1rem; padding: 1rem 1.5rem; text-align: center; min-width: 160px; flex: 1;'>
                                <div style='font-size: 0.75rem; font-weight: 900; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;'>Rata-Rata Nilai</div>
                                <div style='font-size: 2.5rem; font-weight: 900; color: {$warnaPredikat}; line-height: 1;'>{$rataRata}</div>
                                <div style='font-size: 0.75rem; font-weight: 800; color: {$warnaPredikat}; margin-top: 0.5rem; background: " . str_replace('#', '', $warnaPredikat) . "1a; padding: 0.25rem 0.75rem; border-radius: 0.5rem; display: inline-block;'>{$predikat}</div>
                            </div>

                            <!-- Kartu Misi Selesai -->
                            <div style='background: #f0fdf4; border: 2px solid #bbf7d0; border-radius: 1rem; padding: 1rem 1.5rem; text-align: center; min-width: 140px; flex: 1;'>
                                <div style='font-size: 0.75rem; font-weight: 900; color: #166534; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;'>Misi Dinilai</div>
                                <div style='font-size: 2.5rem; font-weight: 900; color: #15803d; line-height: 1;'>{$misiSelesai}</div>
                                <div style='font-size: 0.75rem; font-weight: 800; color: #166534; margin-top: 0.5rem;'>Dari {$totalMisi} Misi</div>
                            </div>

                            <!-- Kartu Perlu Koreksi -->
                            <div style='background: #fff1f2; border: 2px solid #fecdd3; border-radius: 1rem; padding: 1rem 1.5rem; text-align: center; min-width: 140px; flex: 1;'>
                                <div style='font-size: 0.75rem; font-weight: 900; color: #9f1239; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;'>Perlu Dikoreksi</div>
                                <div style='font-size: 2.5rem; font-weight: 900; color: #be123c; line-height: 1;'>{$misiPending}</div>
                                <div style='font-size: 0.75rem; font-weight: 800; color: #9f1239; margin-top: 0.5rem;'>Menunggu</div>
                            </div>

                        </div>
                    </div>
                "))
                ->columnSpanFull(); // 👈 INI OBATNYA AGAR MELEBAR PENUH!

            foreach ($submissions as $submission) {
                $answers = \App\Models\StudentAnswer::with('question')
                    ->where('student_id', $record->id)
                    ->whereHas('question', fn($q) => $q->where('activity_id', $submission->activity_id))
                    ->get()
                    ->sortBy('question_id');

                $questionFields = [];
                $nomor = 1; 
                
                foreach ($answers as $answer) {
                    
                    // ==========================================
                    // 🧠 KECERDASAN BUATAN: PENILAI OTOMATIS (TERPUSAT)
                    // ==========================================
                    $formatSoal = $answer->question->answer_format ?? '';
                    $jawabanMuridMentah = $answer->answer_value ?? $answer->answer_text ?? $answer->answer ?? '';
                    
                    // 1. Tarik Kunci Jawaban via Service
                    $kunciJawabanMentah = AutoGrader::getKunciJawaban($formatSoal, $answer->question);

                    // 👇 [FITUR BARU] INJEKSI KUNCI JAWABAN MATCHING 👇
                    if ($formatSoal === 'matching' && empty($kunciJawabanMentah)) {
                        $optKunci = is_string($answer->question->options) ? json_decode($answer->question->options, true) : ($answer->question->options ?? []);
                        $pasanganBenar = [];
                        
                        foreach ($optKunci as $opt) {
                            $kiri = !empty($opt['teks_pilihan']) ? $opt['teks_pilihan'] : ($opt['image_pilihan'] ?? '');
                            $kanan = !empty($opt['matching_right']) ? $opt['matching_right'] : ($opt['image_matching_right'] ?? '');
                            if ($kiri && $kanan) {
                                $pasanganBenar[$kiri] = $kanan;
                            }
                        }
                        $kunciJawabanMentah = json_encode($pasanganBenar);
                    }
                    
                    // 2. Hitung Skor via Service
                    $skorOtomatis = AutoGrader::periksaSkor($formatSoal, $jawabanMuridMentah, $answer->question);
                    
                    // 3. Konversi Skor ke Status Banner
                    $isJawabanTepat = null; // Default: Butuh manual
                    if ($skorOtomatis === 100) $isJawabanTepat = true;
                    if ($skorOtomatis === 0) $isJawabanTepat = false;

                    $kunciBersih = strtolower(trim(str_replace(['"', "'", '\\', '{', '}', '[', ']'], '', (string)$kunciJawabanMentah)));

                    // ==========================================
                    // SISTEM DETEKTIF GAMBAR (MATCHING)
                    // ==========================================
                    $options = is_string($answer->question->options) ? json_decode($answer->question->options, true) : ($answer->question->options ?? []);
                    $petaGambar = [];
                    
                    if (is_array($options)) {
                        foreach ($options as $opt) {
                            if (!empty($opt['teks_pilihan']) && !empty($opt['image_pilihan'])) {
                                $petaGambar[$opt['teks_pilihan']] = $opt['image_pilihan'];
                            }
                            if (!empty($opt['matching_right']) && !empty($opt['image_matching_right'])) {
                                $petaGambar[$opt['matching_right']] = $opt['image_matching_right'];
                            }
                        }
                    }

                    $renderMatchingBox = function($teks, $peta) {
                        $imgHtml = '';
                        if (isset($peta[$teks])) {
                            $imgUrl = url('private-image/' . $peta[$teks]);
                            $imgHtml = "<div style='background:#f8fafc; border-radius:0.5rem; padding:0.25rem; margin-bottom:0.5rem; border:2px solid #e2e8f0;'><img src='{$imgUrl}' style='width:100%; height:5rem; object-fit:contain; border-radius:0.375rem;' /></div>";
                        }
                        return "<div style='flex:1; background:white; padding:0.75rem; border-radius:1rem; border:3px solid #e2e8f0; text-align:center; box-shadow:0 4px 0 #e2e8f0;'>
                                    {$imgHtml}
                                    <span style='font-weight:900; color:#334155; font-size:0.875rem; text-transform:uppercase;'>{$teks}</span>
                                </div>";
                    };

                    // ==========================================
                    // TAMPILAN HTML JAWABAN MURID (DENGAN DETEKSI WARNA)
                    // ==========================================
                    $jawabanMuridHTML = $jawabanMuridMentah ?: '<div style="text-align:center; padding:1rem;"><span style="background:#e2e8f0; color:#64748b; font-weight:bold; padding:0.5rem 1rem; border-radius:9999px;">Kosong / Tidak Dijawab 🏳️</span></div>';
                    $decodedJawaban = json_decode((string)$jawabanMuridHTML, true);
                    
                    $kunciArray = json_decode((string)$kunciJawabanMentah, true) ?? [];
                    
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedJawaban)) {
                        $html = '<div style="display:flex; flex-direction:column; gap:1rem; margin-top:1rem;">';
                        
                        foreach ($decodedJawaban as $kiri => $kanan) {
                            $isBenar = isset($kunciArray[$kiri]) && $kunciArray[$kiri] === $kanan;
                            
                            $bgWrap = $isBenar ? '#ecfdf5' : '#fff1f2';
                            $borderWrap = $isBenar ? '#6ee7b7' : '#fda4af';
                            $arrowBg = $isBenar ? '#10b981' : '#f43f5e';
                            $icon = $isBenar ? '✔️' : '❌';

                            $html .= "<div style='display:flex; align-items:center; gap:0.5rem; padding:0.75rem; background:{$bgWrap}; border-radius:1.25rem; border:3px solid {$borderWrap};'>";
                            $html .= $renderMatchingBox($kiri, $petaGambar);
                            $html .= "<div style='display:flex; flex-direction:column; align-items:center; justify-content:center; flex-shrink:0; width:3rem;'>
                                        <div style='background:{$arrowBg}; color:white; width:2.5rem; height:2.5rem; display:flex; align-items:center; justify-content:center; border-radius:9999px; font-weight:900; border:2px solid white; box-shadow:0 2px 4px rgba(0,0,0,0.1); font-size:1.25rem;'>
                                            {$icon}
                                        </div>
                                      </div>";
                            $html .= $renderMatchingBox($kanan, $petaGambar);
                            $html .= "</div>";
                        }
                        $html .= '</div>';
                        $jawabanMuridHTML = $html;
                    }

                    // Tampilan HTML Kunci
                    $kunciJawabanHTML = $kunciJawabanMentah ?: '<div style="text-align:center; padding:1rem;"><span style="background:#fef3c7; color:#b45309; font-weight:bold; padding:0.5rem 1rem; border-radius:9999px;">Cek Manual / Subjektif 🧐</span></div>';
                    $decodedKunci = json_decode((string)$kunciJawabanHTML, true);
                    
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedKunci)) {
                        $html = '<div style="display:flex; flex-direction:column; gap:1rem; margin-top:1rem;">';
                        foreach ($decodedKunci as $kiri => $kanan) {
                            $html .= "<div style='display:flex; align-items:center; gap:0.5rem; padding:0.75rem; background:#fffbeb; border-radius:1.25rem; border:3px solid #fde68a;'>";
                            $html .= $renderMatchingBox($kiri, $petaGambar);
                            $html .= "<div style='display:flex; flex-direction:column; align-items:center; justify-content:center; flex-shrink:0; width:3rem;'><div style='background:#fbbf24; color:white; width:2.5rem; height:2.5rem; display:flex; align-items:center; justify-content:center; border-radius:9999px; font-weight:900; border:2px solid white; box-shadow:0 2px 4px rgba(0,0,0,0.1);'>➔</div></div>";
                            $html .= $renderMatchingBox($kanan, $petaGambar);
                            $html .= "</div>";
                        }
                        $html .= '</div>';
                        $kunciJawabanHTML = $html;
                    }

                    $questionFields[] = Forms\Components\Section::make("Tugas Nomor $nomor")
                        ->icon('heroicon-m-sparkles')
                        ->schema([
                            
                            Forms\Components\Placeholder::make("status_mesin_{$answer->id}")
                                ->hiddenLabel()
                                ->content(function () use ($isJawabanTepat, $kunciBersih) {
                                    if ($isJawabanTepat === null) {
                                        return new HtmlString('<div style="display:flex; align-items:center; gap:1rem; background:#faf5ff; color:#581c87; padding:1.25rem; border-radius:1rem; border:3px solid #c4b5fd; box-shadow:0 4px 0 #a855f7;"><span style="font-size:2.5rem;">👩‍🏫🔎</span> <div style="display:flex; flex-direction:column;"><span style="font-weight:900; font-size:1.25rem; text-transform:uppercase;">Butuh Mata Guru!</span><span style="font-size:0.875rem; font-weight:bold; opacity:0.8;">Tidak ada kunci jawaban pasti. Silakan nilai manual.</span></div></div>');
                                    } elseif ($isJawabanTepat === true) {
                                        return new HtmlString('<div style="display:flex; align-items:center; gap:1rem; background:#ecfdf5; color:#064e3b; padding:1.25rem; border-radius:1rem; border:3px solid #6ee7b7; box-shadow:0 4px 0 #34d399;"><span style="font-size:2.5rem;">🤖✅</span> <div style="display:flex; flex-direction:column;"><span style="font-weight:900; font-size:1.25rem; text-transform:uppercase;">Sistem: Jawaban Tepat!</span><span style="font-size:0.875rem; font-weight:bold; opacity:0.8;">Skor 100 otomatis masuk kantong.</span></div></div>');
                                    } else {
                                        return new HtmlString('<div style="display:flex; align-items:center; gap:1rem; background:#fff1f2; color:#881337; padding:1.25rem; border-radius:1rem; border:3px solid #fda4af; box-shadow:0 4px 0 #fb7185;"><span style="font-size:2.5rem;">🤖❌</span> <div style="display:flex; flex-direction:column;"><span style="font-weight:900; font-size:1.25rem; text-transform:uppercase;">Sistem: Jawaban Meleset</span><span style="font-size:0.875rem; font-weight:bold; opacity:0.8;">Periksa lagi, atau biarkan skor 0.</span></div></div>');
                                    }
                                })->columnSpanFull(),

                            Forms\Components\Placeholder::make("soal_{$answer->id}")
                                ->hiddenLabel()
                                ->content(new HtmlString('
                                    <div style="margin-bottom:0.75rem; margin-top:1rem; display:flex; align-items:center; gap:0.5rem;"><span style="background:#334155; color:white; font-weight:900; padding:0.375rem 1rem; border-radius:9999px; font-size:0.75rem; letter-spacing:0.1em; text-transform:uppercase;">Konteks Pertanyaan</span></div>
                                    <div style="padding:1.5rem; background:white; border:3px solid #e2e8f0; border-radius:1.5rem; color:#1e293b; font-size:1.125rem; font-weight:700;">' . $answer->question->question_text . '</div>
                                '))->columnSpanFull(),

                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Placeholder::make("kunci_{$answer->id}")
                                        ->hiddenLabel()
                                        ->content(new HtmlString('
                                            <div style="height:100%; padding:1.5rem; background:#fffbeb; border:3px solid #fcd34d; border-radius:1.5rem;">
                                                <div style="margin-bottom:1.25rem;"><span style="background:#fbbf24; color:#78350f; font-weight:900; padding:0.375rem 1rem; border-radius:9999px; font-size:0.75rem; letter-spacing:0.1em; text-transform:uppercase; border:2px solid white;">🔑 Acuan Jawaban</span></div>
                                                <div style="color:#451a03; font-weight:bold; font-size:1.125rem;">' . $kunciJawabanHTML . '</div>
                                            </div>
                                        ')),

                                    Forms\Components\Placeholder::make("jawaban_{$answer->id}")
                                        ->hiddenLabel()
                                        ->content(new HtmlString('
                                            <div style="height:100%; padding:1.5rem; background:#eff6ff; border:3px solid #93c5fd; border-radius:1.5rem;">
                                                <div style="margin-bottom:1.25rem;"><span style="background:#3b82f6; color:white; font-weight:900; padding:0.375rem 1rem; border-radius:9999px; font-size:0.75rem; letter-spacing:0.1em; text-transform:uppercase; border:2px solid white;">✍️️ Jawaban Murid</span></div>
                                                <div style="color:#1e3a8a; font-weight:900; font-size:1.25rem;">' . $jawabanMuridHTML . '</div>
                                            </div>
                                        ')),
                                ]),

                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make("score_{$answer->id}")
                                        ->label('🎯 Stempel Skor')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->formatStateUsing(function () use ($answer, $skorOtomatis) {
                                            if ($answer->score !== null && $answer->score > 0) {
                                                return $answer->score;
                                            }
                                            return $skorOtomatis ?? 0;
                                        })
                                        ->helperText('Otomatis 100 jika jawaban cocok dengan kunci.')
                                        ->required(),
                                        
                                    Forms\Components\Textarea::make("teacher_notes_{$answer->id}")
                                        ->label('💬 Pesan & Pujian untuk Murid')
                                        ->placeholder('Wah, kamu hebat sekali! Tapi perhatikan lagi bagian...')
                                        ->formatStateUsing(fn () => $answer->teacher_notes)
                                        ->rows(2),
                                ])->extraAttributes(['style' => 'margin-top:1.5rem; background:#f8fafc; padding:1.5rem; border-radius:1.5rem; border:3px solid #e2e8f0;']),

                        ])
                        ->collapsible()
                        ->collapsed(fn() => $submission->status === 'dinilai'); 
                        
                    $nomor++;
                }

                $isPending = $submission->status === 'menunggu_koreksi';
                
                $fields[] = Forms\Components\Section::make("📘 {$submission->activity->module->title} — {$submission->activity->title}")
                    ->description($isPending ? '🔴 Membutuhkan Koreksi Anda' : '🟢 Sudah Selesai Dinilai')
                    ->schema([
                        Forms\Components\Group::make([
                            Forms\Components\Select::make("status_{$submission->id}")
                                ->label('Keputusan Akhir')
                                ->options([
                                    'menunggu_koreksi' => '⏳ Tunda (Belum Selesai)',
                                    'dinilai' => '✅ Selesai Dikoreksi'
                                ])
                                ->formatStateUsing(fn () => $submission->status) 
                                ->required(),
                                
                            Forms\Components\TextInput::make("total_score_{$submission->id}")
                                ->label('Nilai Akhir Aktivitas Ini')
                                ->numeric()
                                ->formatStateUsing(fn () => $submission->total_score)
                                ->disabled() 
                                ->dehydrated(false) 
                                ->extraInputAttributes(['style' => 'font-weight: 900; color: #0284c7; font-size: 1.5rem;']) 
                                ->helperText('🔒 Dikunci. Sistem akan otomatis menghitung rata-rata nilai dari skor soal di bawah saat Anda menekan tombol Save.'),
                        ])->columns(2),

                        Forms\Components\Section::make('Daftar Jawaban')
                            ->schema($questionFields)
                            ->collapsed(!$isPending) 
                    ])
                    ->collapsed(!$isPending); 
            }
            
            return $fields;
        });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvaluations::route('/'),
            'edit' => Pages\EditEvaluation::route('/{record}/edit'),
        ];
    }
}