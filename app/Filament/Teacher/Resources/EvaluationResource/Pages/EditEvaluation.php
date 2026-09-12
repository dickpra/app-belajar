<?php

namespace App\Filament\Teacher\Resources\EvaluationResource\Pages;

use App\Filament\Teacher\Resources\EvaluationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\StudentAnswer;
use App\Models\ActivitySubmission;

class EditEvaluation extends EditRecord
{
    protected static string $resource = EvaluationResource::class;

    // KITA AMBIL ALIH PROSES SAVE AGAR ANTREANNYA TERTIB!
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // 1. ANTREAN PERTAMA: Simpan semua nilai per-soal ke database terlebih dahulu
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'score_')) {
                $answerId = str_replace('score_', '', $key);
                $answer = StudentAnswer::find($answerId);
                if ($answer) {
                    $answer->score = $value;
                    $answer->teacher_notes = $data['teacher_notes_' . $answerId] ?? $answer->teacher_notes;
                    $answer->save(); // 👈 Simpan ke database sekarang!
                }
            }
        }

        // 2. ANTREAN KEDUA: Setelah soal tersimpan, baru kita hitung rata-ratanya
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'status_')) {
                $submissionId = str_replace('status_', '', $key);
                $submission = ActivitySubmission::find($submissionId);
                
                if ($submission) {
                    $submission->status = $value;
                    
                    // Hitung rata-rata SEKARANG (Pasti akurat karena nilai soal sudah di-update di atas)
                    $rataRata = StudentAnswer::where('student_id', $record->id)
                        ->whereHas('question', fn($q) => $q->where('activity_id', $submission->activity_id))
                        ->avg('score');

                    // Masukkan hasil rata-rata ke total skor
                    $submission->total_score = $rataRata ? round($rataRata) : 0;
                    $submission->save(); 
                }
            }
        }

        return $record;
    }
    
    // Setelah klik Save, otomatis kembali ke halaman daftar murid
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}