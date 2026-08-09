<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Activity;
use App\Models\Question;

class ModulUjiCobaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Modul Dummy
        $module = Module::create([
            'title' => 'Modul Spesial: Uji Coba Semua Format Soal',
            'description' => 'Modul ini dibuat otomatis oleh Seeder untuk mengecek kesiapan UI murid.',
            'is_active' => true,
        ]);

        // 2. Buat Aktivitas dengan Gerbong Tahapan (Tiptap Ready)
        $activity = $module->activities()->create([
            'title' => 'Aktivitas 1.1: Simulasi Ujian Interaktif',
            'stage_type' => 'materi',
            'description' => '<p>Deskripsi cadangan jika stages kosong.</p>',
            'stages' => [
                [
                    'tipe_tahapan' => 'materi',
                    'konten_tahapan' => '<p>Halo! Di aktivitas ini kita akan melihat bagaimana semua format soal ditampilkan di layar. Silakan klik <strong>Mulai Berlatih</strong> di bawah!</p>'
                ]
            ],
            'assessment_metrics' => [
                'numerasi' => ['ML', 'MP'],
                'fase' => ['Kon'],
                'steam' => ['S', 'M']
            ],
        ]);

        // 3. Siapkan Array Lengkap Semua Tipe Soal
        $questions = [
            // TIPE 1: Pilihan Ganda Standar
            [
                'question_text' => '<p>Berapakah hasil dari <strong>15 + 20</strong>?</p>',
                'answer_format' => 'multiple_choice',
                'layout_position' => 'image_top',
                'options' => [
                    ['teks_pilihan' => '25', 'is_correct' => false],
                    ['teks_pilihan' => '35', 'is_correct' => true],
                    ['teks_pilihan' => '45', 'is_correct' => false],
                ],
            ],
            
            // TIPE 2: Input Angka Tunggal
            [
                'question_text' => '<p>Ibu membeli 3 kotak pensil. Jika setiap kotak berisi 10 pensil, berapakah total pensil yang dibeli ibu? <em>(Ketik angkanya saja)</em></p>',
                'answer_format' => 'number_input',
                'layout_position' => 'image_left',
                'correct_answer' => '30',
                'options' => null,
            ],

            // TIPE 3: Input Teks Singkat
            [
                'question_text' => '<p>Benda langit yang bersinar di siang hari dan menjadi pusat tata surya kita disebut...</p>',
                'answer_format' => 'text_input',
                'layout_position' => 'image_right',
                'correct_answer' => 'Matahari',
                'options' => null,
            ],

            // TIPE 4: Menjodohkan (Matching)
            [
                'question_text' => '<p>Pasangkanlah nama hewan di bawah ini dengan jumlah kakinya yang tepat!</p>',
                'answer_format' => 'matching',
                'layout_position' => 'image_bottom',
                'options' => [
                    ['teks_pilihan' => 'Ayam', 'matching_right' => '2 Kaki', 'is_correct' => false],
                    ['teks_pilihan' => 'Kucing', 'matching_right' => '4 Kaki', 'is_correct' => false],
                    ['teks_pilihan' => 'Laba-laba', 'matching_right' => '8 Kaki', 'is_correct' => false],
                ],
            ],

            // TIPE 5: Benar/Salah + Koreksi
            [
                'question_text' => '<p>Arah matahari terbit adalah dari sebelah Barat. Tentukan apakah pernyataan ini Benar atau Salah!</p>',
                'answer_format' => 'true_false_correction',
                'layout_position' => 'image_left',
                'options' => [
                    ['teks_pilihan' => 'Benar', 'is_correct' => false],
                    ['teks_pilihan' => 'Salah (Yang benar adalah Timur)', 'is_correct' => true],
                ],
            ],

            // TIPE 6: Isian Rumpang (Complex Fill)
            [
                'question_text' => '<p>Lengkapilah kalimat berikut dengan memilih kata yang tepat!</p><p>Saya makan nasi menggunakan ____ dan ____.</p>',
                'answer_format' => 'complex_fill',
                'layout_position' => 'image_top',
                'options' => [
                    ['teks_pilihan' => 'Sendok', 'is_correct' => true],
                    ['teks_pilihan' => 'Garpu', 'is_correct' => true],
                    ['teks_pilihan' => 'Cangkul', 'is_correct' => false],
                ],
            ],
        ];

        // 4. Masukkan ke Database
        foreach ($questions as $q) {
            $activity->questions()->create($q);
        }
    }
}