<?php

namespace App\Filament\Admin\Resources\ModuleResource\Pages;

use App\Filament\Admin\Resources\ModuleResource;
use App\Models\Module;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ListModules extends ListRecords
{
    protected static string $resource = ModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('+ Rancang Modul Baru'),
            
            // ==========================================
            // TOMBOL IMPORT ZIP
            // ==========================================
            Actions\Action::make('import_zip')
                ->label('Import Modul (ZIP)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->form([
                    Forms\Components\FileUpload::make('zip_file')
                        ->label('Upload File ZIP Modul')
                        ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed'])
                        ->disk('local') 
                        ->directory('temp_import')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $zipPath = storage_path('app/' . $data['zip_file']);
                    $zip = new \ZipArchive();

                    if ($zip->open($zipPath) === TRUE) {
                        $extractPath = storage_path('app/temp_import/ekstrak_' . time());
                        $zip->extractTo($extractPath);
                        $zip->close();

                        $jsonFile = $extractPath . '/data_modul.json';
                        
                        if (File::exists($jsonFile)) {
                            
                            // 👇 1. TARIK TEKS MENTAH JSON 👇
                            $rawJson = File::get($jsonFile);
                            
                            // 👇 2. MESIN ADAPTOR URL OTOMATIS 👇
                            // Mendeteksi semua link gambar/video/audio lama (seperti http://127.0.0.1/private-image...) 
                            // lalu menggantinya dengan URL aplikasi yang sedang berjalan sekarang secara dinamis!
                            $currentUrl = rtrim(config('app.url'), '/');
                            
                            // PERBAIKAN: Tambahkan 'audio' ke dalam deteksi regex
                            $rawJson = preg_replace('/https?:\/\/[^\/]+\/private-(image|video|audio)/i', $currentUrl . '/private-$1', $rawJson);
                            
                            // 👇 3. UBAH JADI ARRAY SETELAH URLNYA BERSIH 👇
                            $moduleData = json_decode($rawJson, true);

                            // 1. Buat Modul Utama
                            $newModule = Module::create([
                                'title' => $moduleData['title'] . ' (Import)',
                                'description' => $moduleData['description'] ?? null,
                                'access_pin' => $moduleData['access_pin'] ?? null,
                                'is_adaptive' => $moduleData['is_adaptive'] ?? false,
                                'is_active' => false, // Set Draft untuk keamanan
                                'is_instant_mode' => $moduleData['is_instant_mode'] ?? false, // Jangan lupa mode instan dibawa juga!
                            ]);

                            // 2. Pindahkan folder 'files' dari dalam ZIP ke folder sistem asli
                            if (File::isDirectory($extractPath . '/files')) {
                                File::copyDirectory($extractPath . '/files', storage_path('app/'));
                            }

                            // 3. Masukkan Aktivitas, Tahapan, dan Soal
                            if (!empty($moduleData['activities'])) {
                                foreach ($moduleData['activities'] as $actData) {
                                    $newActivity = $newModule->activities()->create([
                                        'title' => $actData['title'],
                                        'description' => $actData['description'] ?? null,
                                        'assessment_metrics' => $actData['assessment_metrics'],
                                        'stages' => is_array($actData['stages']) ? $actData['stages'] : json_decode($actData['stages'], true),
                                    ]);

                                    if (!empty($actData['questions'])) {
                                        foreach ($actData['questions'] as $qData) {
                                            $newActivity->questions()->create([
                                                'answer_format' => $qData['answer_format'],
                                                'layout_position' => $qData['layout_position'] ?? 'bottom',
                                                'difficulty' => $qData['difficulty'],
                                                'question_text' => $qData['question_text'],
                                                'image' => $qData['image'] ?? null,
                                                'sign_language_video' => $qData['sign_language_video'] ?? null,
                                                'voice_note' => $qData['voice_note'] ?? null, // 👈 TAMBAHAN: Tarik data voice note
                                                'correct_answer' => $qData['correct_answer'] ?? null,
                                                'true_false_answer' => $qData['true_false_answer'] ?? null,
                                                'correction_text' => $qData['correction_text'] ?? null,
                                                'answer_explanation' => $qData['answer_explanation'] ?? null,
                                                'options' => is_array($qData['options']) ? $qData['options'] : json_decode($qData['options'], true),
                                            ]);
                                        }
                                    }
                                }
                            }

                            // Bersihkan file temporary
                            File::deleteDirectory($extractPath);
                            File::delete($zipPath);

                            Notification::make()
                                ->title('Modul Berhasil Diimpor!')
                                ->body('Seluruh tahapan, soal, dan gambar berhasil dipulihkan dengan URL baru.')
                                ->success()
                                ->send();

                        } else {
                            Notification::make()
                                ->title('Import Gagal')
                                ->body('File data_modul.json tidak ditemukan dalam file ZIP.')
                                ->danger()
                                ->send();
                        }
                    } else {
                        Notification::make()
                            ->title('Import Gagal')
                            ->body('File ZIP rusak atau tidak bisa dibuka.')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}