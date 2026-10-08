<?php

namespace App\Filament\Admin\Resources\SignDictionaryResource\Pages;

use App\Filament\Admin\Resources\SignDictionaryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\SignDictionary;

class ListSignDictionaries extends ListRecords
{
    protected static string $resource = SignDictionaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('+ Tambah Kosakata'),

            // ==========================================
            // TOMBOL EXPORT (BACKUP) ZIP
            // ==========================================
            Actions\Action::make('export_zip')
                ->label('Backup Kamus (ZIP)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $dictionaries = SignDictionary::all();
                    
                    if ($dictionaries->isEmpty()) {
                        Notification::make()
                            ->title('Gagal Backup')
                            ->body('Belum ada data kamus untuk dibackup.')
                            ->warning()
                            ->send();
                        return;
                    }

                    // 1. Siapkan file ZIP
                    $zipFileName = 'Backup_Kamus_Isyarat_' . date('Ymd_His') . '.zip';
                    $zipPath = storage_path('app/temp_export/' . $zipFileName);
                    
                    File::ensureDirectoryExists(storage_path('app/temp_export'));
                    
                    $zip = new \ZipArchive();
                    if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                        
                        $jsonData = [];
                        
                        foreach ($dictionaries as $dict) {
                            // Masukkan data ke array JSON
                            $jsonData[] = [
                                'word' => $dict->word,
                                'video_path' => $dict->video_path
                            ];
                            
                            // 2. Cari file video aslinya di disk modul_rahasia
                            $videoFullPath = Storage::disk('modul_rahasia')->path($dict->video_path);
                            
                            if (File::exists($videoFullPath)) {
                                // 3. Masukkan video fisik ke dalam folder 'videos' di dalam file ZIP
                                $zip->addFile($videoFullPath, 'videos/' . basename($dict->video_path));
                            }
                        }
                        
                        // 4. Masukkan catatan JSON ke dalam ZIP
                        $zip->addFromString('kamus_isyarat.json', json_encode($jsonData, JSON_PRETTY_PRINT));
                        $zip->close();
                        
                        // 5. Download ZIP dan langsung hapus file sementaranya agar server tidak penuh
                        return response()->download($zipPath)->deleteFileAfterSend(true);
                        
                    } else {
                        Notification::make()
                            ->title('Sistem Error')
                            ->body('Gagal membuat file ZIP Backup.')
                            ->danger()
                            ->send();
                    }
                }),

            // ==========================================
            // TOMBOL IMPORT (RESTORE) ZIP
            // ==========================================
            Actions\Action::make('import_zip')
                ->label('Restore Kamus (ZIP)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->form([
                    FileUpload::make('zip_file')
                        ->label('Upload File Backup ZIP')
                        ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed', 'application/octet-stream'])
                        ->maxSize(102400) // Maks 100MB (Karena isi video)
                        ->disk('local')
                        ->directory('temp_import')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $zipPath = storage_path('app/' . $data['zip_file']);
                    $zip = new \ZipArchive();
                    
                    if ($zip->open($zipPath) === TRUE) {
                        $extractPath = storage_path('app/temp_import/ekstrak_kamus_' . time());
                        $zip->extractTo($extractPath);
                        $zip->close();
                        
                        $jsonFile = $extractPath . '/kamus_isyarat.json';
                        
                        if (File::exists($jsonFile)) {
                            $jsonData = json_decode(File::get($jsonFile), true);
                            $berhasil = 0;
                            
                            foreach ($jsonData as $item) {
                                $word = $item['word'];
                                $videoPath = $item['video_path'];
                                
                                // 1. Pindahkan video dari folder ekstrak ke tempat aslinya di modul_rahasia
                                $videoSourcePath = $extractPath . '/videos/' . basename($videoPath);
                                
                                if (File::exists($videoSourcePath)) {
                                    $targetDir = Storage::disk('modul_rahasia')->path('kamus_isyarat');
                                    File::ensureDirectoryExists($targetDir);
                                    
                                    $targetFullPath = Storage::disk('modul_rahasia')->path($videoPath);
                                    File::copy($videoSourcePath, $targetFullPath);
                                }
                                
                                // 2. Simpan ke Database (Otomatis timpa jika kata sudah ada)
                                SignDictionary::updateOrCreate(
                                    ['word' => $word],
                                    ['video_path' => $videoPath]
                                );
                                
                                $berhasil++;
                            }
                            
                            // 3. Sapu bersih file sampah (Ekstraksi & ZIP awal)
                            File::deleteDirectory($extractPath);
                            File::delete($zipPath);
                            
                            Notification::make()
                                ->title('Restore Selesai!')
                                ->body("Berhasil memulihkan $berhasil kosakata beserta videonya.")
                                ->success()
                                ->send();
                                
                        } else {
                            Notification::make()
                                ->title('Import Ditolak')
                                ->body('File kamus_isyarat.json tidak ditemukan di dalam ZIP. Ini bukan file backup yang sah.')
                                ->danger()
                                ->send();
                        }
                    } else {
                        Notification::make()
                            ->title('Import Gagal')
                            ->body('File ZIP rusak atau tidak terbaca.')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
