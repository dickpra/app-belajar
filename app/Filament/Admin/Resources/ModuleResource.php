<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ModuleResource\Pages;
use App\Models\Module;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
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
        return $form->schema([
            
            Forms\Components\Section::make('Informasi Modul Utama')
                ->description('Atur identitas dasar dan akses modul ini.')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->label('Judul Modul (Misal: Modul Minggu ke-8)'),
                    
                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi Singkat (Opsional)'),
                    
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('access_pin')
                            ->label('PIN Akses Modul (Opsional)')
                            ->placeholder('Biarkan kosong jika tidak pakai PIN')
                            ->maxLength(6)
                            ->numeric(),

                        Forms\Components\Grid::make(1)->schema([
                            Forms\Components\Toggle::make('is_adaptive')
                                ->label('🤖 Aktifkan Mode Pembelajaran Adaptif?'),
                                
                            Forms\Components\Toggle::make('is_active')
                                ->default(true)
                                ->label('Modul Aktif?'),
                        ])
                    ]),
                ])
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
            // 👇 TAMBAHKAN DUA BARIS INI 👇
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            // 👆 ======================= 👆
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // ==========================================
                // TOMBOL EXPORT ZIP (DIKEMBALIKAN SESUAI KODE ASLI)
                // ==========================================
                Tables\Actions\Action::make('export_zip')
                    ->label('Export Modul')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('success')
                    ->action(function (\App\Models\Module $record) {
                        // 1. Tarik semua data anak-cucunya
                        $record->load(['activities.questions']);
                        $moduleData = $record->toArray();

                        // Siapkan nama file & path sementara
                        $zipFileName = 'Eksport_Modul_' . Str::slug($record->title) . '_' . date('Ymd_His') . '.zip';
                        $tempDir = storage_path('app/temp_eksport');
                        if (!File::isDirectory($tempDir)) {
                            File::makeDirectory($tempDir, 0755, true);
                        }
                        $zipPath = $tempDir . '/' . $zipFileName;

                        $zip = new \ZipArchive();
                        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                            
                            // 2. Masukkan Database sebagai JSON
                            $zip->addFromString('data_modul.json', json_encode($moduleData, JSON_PRETTY_PRINT));
                            
                            $filesToZip = [];

                            // Helper untuk mengekstrak path gambar dari dalam Rich Text Editor
                            $extractHtmlImages = function($html) use (&$filesToZip) {
                                if (!$html) return;
                                // Menangkap path yang mengandung 'modul_private'
                                preg_match_all('/modul_private\/([a-zA-Z0-9\-\_\.\/]+)/', $html, $matches);
                                if (!empty($matches[1])) {
                                    foreach ($matches[1] as $img) {
                                        $filesToZip[] = 'modul_private/' . urldecode($img);
                                    }
                                }
                            };

                            // 3. Kumpulkan semua path file fisik
                            foreach ($record->activities as $activity) {
                                // Tahapan
                                $stages = is_string($activity->stages) ? json_decode($activity->stages, true) : $activity->stages;
                                if (is_array($stages)) {
                                    foreach ($stages as $stage) {
                                        if (!empty($stage['sign_language_video'])) $filesToZip[] = $stage['sign_language_video'];
                                        if (!empty($stage['konten_tahapan'])) $extractHtmlImages($stage['konten_tahapan']);
                                    }
                                }

                                // Soal & Opsi
                                foreach ($activity->questions as $question) {
                                    if (!empty($question->image)) $filesToZip[] = $question->image;
                                    if (!empty($question->sign_language_video)) $filesToZip[] = $question->sign_language_video;
                                    if (!empty($question->question_text)) $extractHtmlImages($question->question_text);
                                    if (!empty($question->answer_explanation)) $extractHtmlImages($question->answer_explanation);

                                    $options = is_string($question->options) ? json_decode($question->options, true) : $question->options;
                                    if (is_array($options)) {
                                        foreach ($options as $opt) {
                                            if (!empty($opt['image_pilihan'])) $filesToZip[] = $opt['image_pilihan'];
                                            if (!empty($opt['image_matching_right'])) $filesToZip[] = $opt['image_matching_right'];
                                        }
                                    }
                                }
                            }

                            // Bersihkan duplikat
                            $filesToZip = array_unique(array_filter($filesToZip)); 

                            // 4. Masukkan file fisik ke dalam ZIP (di dalam folder 'files/')
                            foreach ($filesToZip as $filePath) {
                                // Asumsi disk 'modul_rahasia' berada di storage/app/
                                $fullPath = storage_path('app/' . ltrim($filePath, '/')); 
                                
                                if (File::exists($fullPath)) {
                                    $zip->addFile($fullPath, 'files/' . ltrim($filePath, '/')); 
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

    public static function getRelations(): array
    {
        return [
            // Memanggil Relation Manager untuk Aktivitas
            \App\Filament\Admin\Resources\ModuleResource\RelationManagers\ActivitiesRelationManager::class,
        ];
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