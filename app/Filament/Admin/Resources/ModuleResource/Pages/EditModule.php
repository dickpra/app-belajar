<?php

namespace App\Filament\Admin\Resources\ModuleResource\Pages;

use App\Filament\Admin\Resources\ModuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use App\Models\Activity;
use App\Models\Question;

class EditModule extends EditRecord
{
    protected static string $resource = ModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // TOMBOL AJAIB IMPORT JSON
            // Actions\Action::make('import_json')
            //     ->label('⚡ Import via JSON')
            //     ->color('warning')
            //     ->icon('heroicon-o-bolt')
            //     ->form([
            //         Textarea::make('json_data')
            //             ->label('Paste kode JSON dari AI di sini')
            //             ->placeholder('{"code": "1.1", "title": "...", ...}')
            //             ->required()
            //             ->rows(15),
            //     ])
            //     ->action(function (array $data) {
            //         // 1. Cek validitas JSON
            //         $json = json_decode($data['json_data'], true);
                    
            //         if (!$json) {
            //             Notification::make()
            //                 ->title('Gagal! Format JSON berantakan atau tidak valid.')
            //                 ->danger()
            //                 ->send();
            //             return;
            //         }

            //         // 2. Merakit HTML untuk Editor Tiptap
            //         $html = "<h2>Tujuan Pembelajaran</h2>";
            //         $html .= "<p>" . ($json['objective'] ?? '') . "</p>";

            //         // MERAKIT LANGKAH KEGIATAN
            //         if (!empty($json['learning_steps'])) {
            //             $html .= "<h2>Langkah Kegiatan</h2><ul>";
            //             foreach ($json['learning_steps'] as $step) {
            //                 $html .= "<li>{$step}</li>";
            //             }
            //             $html .= "</ul>";
            //         }

            //         // MERAKIT PERTANYAAN PEMANTIK (TABEL)
            //         if (!empty($json['trigger_questions'])) {
            //             $html .= "<h2>Pertanyaan Pemantik</h2>";
            //             $html .= "<table border='1' style='width:100%; border-collapse: collapse;'>";
            //             $html .= "<thead><tr><th>Kartu</th><th>Konteks</th><th>Pertanyaan</th></tr></thead><tbody>";
            //             foreach ($json['trigger_questions'] as $tq) {
            //                 $html .= "<tr>";
            //                 $html .= "<td><strong>{$tq['title']}</strong></td>";
            //                 $html .= "<td>{$tq['content']}</td>";
            //                 $html .= "<td>{$tq['question']}</td>";
            //                 $html .= "</tr>";
            //             }
            //             $html .= "</tbody></table><p><br></p>";
            //         }

            //         // MERAKIT BLOK OBSERVASI (SANGAT LENGKAP)
            //         if (!empty($json['observation'])) {
            //             $obs = $json['observation'];
            //             $html .= "<h2>{$obs['title']}</h2>";
            //             $html .= "<p><strong>{$obs['introduction']}</strong></p>";

            //             // Render Contoh Gambar
            //             if (!empty($obs['examples'])) {
            //                 $html .= "<ul>";
            //                 foreach ($obs['examples'] as $ex) {
            //                     $html .= "<li>{$ex}</li>";
            //                 }
            //                 $html .= "</ul>";
            //             }

            //             // Render Deskripsi Narasi
            //             if (!empty($obs['description'])) {
            //                 foreach ($obs['description'] as $desc) {
            //                     $html .= "<p>{$desc}</p>";
            //                 }
            //             }

            //             // Render Aktivitas Bersama
            //             if (!empty($obs['activities'])) {
            //                 $html .= "<h3>Kegiatan Bersama Guru</h3><ul>";
            //                 foreach ($obs['activities'] as $act) {
            //                     $html .= "<li>{$act}</li>";
            //                 }
            //                 $html .= "</ul>";
            //             }

            //             // Render Pertanyaan Guru
            //             if (!empty($obs['teacher_questions'])) {
            //                 $html .= "<h3>Pertanyaan untuk Siswa</h3><ul>";
            //                 foreach ($obs['teacher_questions'] as $tq) {
            //                     $html .= "<li>{$tq}</li>";
            //                 }
            //                 $html .= "</ul>";
            //             }
            //         }

            //         // MERAKIT KONSEP PENTING
            //         if (!empty($json['important_concepts'])) {
            //             $html .= "<h2>Konsep Penting!!!</h2><ul>";
            //             foreach ($json['important_concepts'] as $concept) {
            //                 $html .= "<li><strong>{$concept['title']}:</strong> {$concept['content']}</li>";
            //             }
            //             $html .= "</ul>";
            //         }

            //         // 3. Simpan ke Database Tabel Activities (Tersambung ke Modul ini)
            //         $activity = Activity::create([
            //             'module_id'   => $this->record->id, // Mengambil ID modul yang sedang diedit
            //             'title'       => "Aktivitas " . ($json['code'] ?? '') . ": " . ($json['title'] ?? 'Tanpa Judul'),
            //             'description' => $html,
            //         ]);

            //         // 4. Simpan Soal Latihan ke Tabel Questions
            //         if (!empty($json['exercises'])) {
            //             foreach ($json['exercises'] as $ex) {
            //                 Question::create([
            //                     'activity_id'     => $activity->id,
            //                     'question_text'   => "<p>{$ex['question']}</p>",
            //                     'answer_format'   => 'text_input', // Set default
            //                     'layout_position' => 'image_top',
            //                 ]);
            //             }
            //         }

            //         // 5. Sukses dan Refresh Halaman
            //         Notification::make()
            //             ->title('Sukses! Data Aktivitas & Soal berhasil disuntikkan.')
            //             ->success()
            //             ->send();

            //         // Refresh halaman agar tabel repeater di bawah ikut terupdate
            //         redirect(request()->header('Referer'));
            //     }),
                
            Actions\DeleteAction::make(),
        ];
    }
}
