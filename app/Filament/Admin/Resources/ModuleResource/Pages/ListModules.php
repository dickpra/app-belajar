<?php

namespace App\Filament\Admin\Resources\ModuleResource\Pages;

use App\Filament\Admin\Resources\ModuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\Module;

class ListModules extends ListRecords
{
    protected static string $resource = ModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import_zip')
                ->label('Import Modul (ZIP)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('zip_file')
                        ->label('Upload File Backup ZIP')
                        ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed', 'application/zip-compressed'])
                        ->disk('local') // Simpan sementara di storage/app/
                        ->directory('temp_imports')
                        ->required(),
                ])
                ->action(function (array $data) {
                    // 1. Cek apakah file berhasil masuk
                    if (empty($data['zip_file'])) {
                        Notification::make()->title('File gagal diunggah!')->danger()->send();
                        return;
                    }

                    // 2. Ambil jalur absolut file ZIP tersebut
                    $filePath = $data['zip_file'];
                    $fullPath = Storage::disk('local')->path($filePath);

                    $zip = new \ZipArchive;
                    $extractPath = storage_path('app/temp_import_' . time());

                    // 3. Ekstrak ZIP
                    if ($zip->open($fullPath) === TRUE) {
                        $zip->extractTo($extractPath);
                        $zip->close();

                        // 4. Baca File JSON
                        $jsonPath = $extractPath . '/database_modul.json';
                        if (!File::exists($jsonPath)) {
                            Notification::make()->title('Gagal: File database_modul.json tidak ditemukan di dalam ZIP!')->danger()->send();
                            File::deleteDirectory($extractPath);
                            Storage::disk('local')->delete($filePath);
                            return;
                        }

                        $modulData = json_decode(File::get($jsonPath), true);

                        // 5. Insert Data Modul Baru ke Database
                        $newModule = Module::create([
                            'title' => $modulData['title'] . ' (Imported)',
                            'description' => $modulData['description'] ?? null,
                            // Tambahkan field modul lainnya jika ada (seperti is_active)
                        ]);

                        // 6. Masukkan Aktivitas & Soal beserta metrik matriksnya
                        foreach ($modulData['activities'] ?? [] as $actData) {
                            $newActivity = $newModule->activities()->create([
                                'title' => $actData['title'],
                                'description' => $actData['description'],
                                'assessment_metrics' => $actData['assessment_metrics'] ?? null,
                                'image' => $actData['image'] ?? null,
                            ]);

                            foreach ($actData['questions'] ?? [] as $qData) {
                                $newActivity->questions()->create([
                                    'question_text' => $qData['question_text'],
                                    'answer_format' => $qData['answer_format'],
                                    'options' => $qData['options'] ?? null,
                                    'correct_answer' => $qData['correct_answer'] ?? null,
                                    'answer_explanation' => $qData['answer_explanation'] ?? null,
                                    'image' => $qData['image'] ?? null,
                                ]);
                            }
                        }

                        // 7. Pindahkan file gambar dari folder ZIP ke Storage Public
                        if (File::isDirectory($extractPath . '/images')) {
                            File::copyDirectory($extractPath . '/images', storage_path('app/public'));
                        }

                        // 8. Bersihkan sampah file temp dan zip
                        File::deleteDirectory($extractPath);
                        Storage::disk('local')->delete($filePath);

                        Notification::make()->title('Hore! Import Modul Berhasil! 🎉')->success()->send();
                    } else {
                        Notification::make()->title('Gagal: File ZIP rusak atau tidak bisa dibaca.')->danger()->send();
                        Storage::disk('local')->delete($filePath);
                    }
                }),

            Actions\CreateAction::make(),
        ];
    }
}
