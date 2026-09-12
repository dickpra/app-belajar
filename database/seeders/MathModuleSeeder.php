<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MathModuleSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ==========================================
        // FUNGSI HELPER: Penyeragaman Kolom Pertanyaan (ANTI ERROR SQL)
        // ==========================================
        $formatQuestion = function ($data) use ($now) {
            return array_merge([
                'image' => null,
                'sign_language_video' => null,
                'correct_answer' => null,
                'true_false_answer' => null,
                'correction_text' => null,
                'answer_explanation' => null,
                'layout_position' => 'image_top',
                'options' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ], $data);
        };

        // ==========================================
        // 0. INSERT MODUL UTAMA
        // ==========================================
        $moduleId = DB::table('modules')->insertGetId([
            'title' => 'Topik: Bilangan (Fase A) - Minggu 3',
            'description' => 'Menyusun bilangan cacah 21-50 sebagai kombinasi puluhan dan satuan',
            'is_active' => true,
            'is_adaptive' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // ==========================================
        // 1. AKTIVITAS 1.1: Mengelompokkan Benda
        // ==========================================
        $act1_1 = DB::table('activities')->insertGetId([
            'module_id' => $moduleId,
            'title' => 'Aktivitas 1.1 Mengelompokkan Benda ke dalam Wadah Berkapasitas 10',
            'stage_type' => 'materi',
            'assessment_metrics' => json_encode([
                'numerasi' => ['ML', 'MP', 'CeT'], 
                'fase' => ['Kon', 'Pik'], 
                'steam' => ['M', 'A']
            ]),
            'stages' => json_encode([
                [
                    'tipe_tahapan' => 'materi', 
                    'konten_tahapan' => '<p><strong>Tujuan Aktivitas:</strong> Melalui penggunaan benda konkret, siswa mampu membentuk kelompok puluhan dengan cara mengelompokkan benda ke dalam wadah yang masing-masing berisi 10 benda.</p>'
                ],
                [
                    'tipe_tahapan' => 'berpikir', 
                    'konten_tahapan' => '<p><strong>Pertanyaan Pemantik:</strong><br>1. Mana yang lebih mudah dihitung?<br>2. Kalau wadah sudah penuh, apa perlu dihitung lagi?<br>3. Mengapa hasilnya 24?</p>'
                ],
                [
                    'tipe_tahapan' => 'amati', 
                    'konten_tahapan' => '<p><strong>Ayo Mengamati:</strong> Untuk menghitung banyak benda, kita dapat mengelompokkan setiap 10 benda. Contoh: Satu kotak berisi 10 penghapus, Satu wadah berisi 10 telur, Satu ikat berisi 10 pensil.</p>'
                ],
                [
                    'tipe_tahapan' => 'simpulkan', 
                    'konten_tahapan' => '<p><strong>Konsep Penting!!!</strong><br>Puluhan sebagai Satu Unit: Sepuluh satuan dapat digabungkan menjadi satu puluhan.<br>Nilai Tempat: Setiap bilangan dua digit tersusun atas digit puluhan dan digit satuan.</p>'
                ]
            ]),
            'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('questions')->insert([
            $formatQuestion([
                'activity_id' => $act1_1,
                'question_text' => '<p>Perhatikan gambar bola pingpong di dalam kotak. <strong>Berapa banyak bola semuanya? _____</strong></p>',
                'answer_format' => 'number_input',
                'difficulty' => 'easy',
                'correct_answer' => '24',
            ]),
            $formatQuestion([
                'activity_id' => $act1_1,
                'question_text' => '<p>Perhatikan susunan mobil mainan. Terdapat 3 kotak penuh dan 2 mobil di luar kotak. <strong>Bilangan yang terbentuk adalah ________</strong></p>',
                'answer_format' => 'number_input',
                'difficulty' => 'easy',
                'correct_answer' => '32',
            ]),
            $formatQuestion([
                'activity_id' => $act1_1,
                'question_text' => '<p>Terdapat 4 bungkus kelereng (masing-masing berisi 10). <strong>Berapa banyak kelereng semuanya? ________</strong></p>',
                'answer_format' => 'number_input',
                'difficulty' => 'easy',
                'correct_answer' => '40',
            ]),
        ]);

        // ==========================================
        // 2. AKTIVITAS 1.2: Menentukan banyak puluhan dan satuan
        // ==========================================
        $act1_2 = DB::table('activities')->insertGetId([
            'module_id' => $moduleId,
            'title' => 'Aktivitas 1.2. Menentukan banyak puluhan dan satuan dari hasil pengelompokan benda',
            'stage_type' => 'materi',
            'assessment_metrics' => json_encode([
                'numerasi' => ['ML', 'PS', 'CeT'], 
                'fase' => ['Kon', 'Pik'], 
                'steam' => []
            ]),
            'stages' => json_encode([
                [
                    'tipe_tahapan' => 'materi', 
                    'konten_tahapan' => '<p><strong>Tujuan Aktivitas:</strong> Melalui kegiatan memverifikasi hasil pengelompokan benda terhadap bilangan target, siswa mampu mencocokkan susunan wadah dan sisa benda dengan bilangan yang dimaksud, serta mengidentifikasi letak kekeliruan jika tidak cocok.</p>'
                ],
                [
                    'tipe_tahapan' => 'amati', 
                    'konten_tahapan' => '<p><strong>Memahami Puluhan dan Satuan:</strong> Pada bilangan dua angka, angka pertama menunjukkan banyaknya puluhan. Angka kedua menunjukkan banyaknya satuan. Contoh: Bilangan 35 berarti 3 puluhan dan 5 satuan.</p>'
                ],
            ]),
            'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('questions')->insert([
            $formatQuestion([
                'activity_id' => $act1_2,
                'question_text' => '<p>1. Dimas memiliki 24 kancing. Ia memasukkan kancing ke dalam wadah. Setiap wadah hanya dapat menampung 10 kancing.<br><strong>Ada berapa wadah yang terisi penuh?</strong></p>',
                'answer_format' => 'number_input',
                'difficulty' => 'easy',
                'correct_answer' => '2',
            ]),
            $formatQuestion([
                'activity_id' => $act1_2,
                'question_text' => '<p><strong>Berapa kancing yang tersisa?</strong></p>',
                'answer_format' => 'number_input',
                'difficulty' => 'easy',
                'correct_answer' => '4',
            ]),
            $formatQuestion([
                'activity_id' => $act1_2,
                'question_text' => '<p>2. Budi memiliki 35 payung. Budi akan memasukkan payung ke dalam wadah. Satu wadah dapat menampung 10 payung. Tersedia 3 wadah.<br><strong>Berapa wadah yang terisi penuh?</strong></p>',
                'answer_format' => 'number_input',
                'difficulty' => 'easy',
                'correct_answer' => '3',
            ]),
            $formatQuestion([
                'activity_id' => $act1_2,
                'question_text' => '<p><strong>Berapa payung yang tidak masuk ke dalam wadah?</strong></p>',
                'answer_format' => 'number_input',
                'difficulty' => 'easy',
                'correct_answer' => '5',
            ]),
            $formatQuestion([
                'activity_id' => $act1_2,
                'question_text' => '<p>3. Andi mempunyai 37 pensil. Ia membuat 1 ikatan berisi 10 pensil.<br><strong>Berapa ikatan yang dapat dibuat?</strong></p>',
                'answer_format' => 'number_input',
                'difficulty' => 'medium',
                'correct_answer' => '3',
            ]),
            $formatQuestion([
                'activity_id' => $act1_2,
                'question_text' => '<p><strong>Dan berapa pensil yang tersisa?</strong></p>',
                'answer_format' => 'number_input',
                'difficulty' => 'medium',
                'correct_answer' => '7',
            ]),
            $formatQuestion([
                'activity_id' => $act1_2,
                'question_text' => '<p>4. Budi memiliki 4 kantong kelereng. Setiap kantong berisi 10 kelereng. Masih ada 5 kelereng di luar kantong.<br><strong>Berapa jumlah seluruh kelereng Budi?</strong></p>',
                'answer_format' => 'number_input',
                'difficulty' => 'medium',
                'correct_answer' => '45',
            ]),
        ]);

        // ==========================================
        // 3. AKTIVITAS 1.3: Menggabungkan dan Menjodohkan
        // ==========================================
        $act1_3 = DB::table('activities')->insertGetId([
            'module_id' => $moduleId,
            'title' => 'Aktivitas 1.3. Menggabungkan puluhan dan satuan menjadi bilangan cacah, lalu menuliskannya',
            'stage_type' => 'materi',
            'assessment_metrics' => json_encode([
                'numerasi' => ['ML', 'MP', 'CeT'], 
                'fase' => ['Pik'], 
                'steam' => []
            ]),
            'stages' => json_encode([
                [
                    'tipe_tahapan' => 'materi', 
                    'konten_tahapan' => '<p><strong>Tujuan Aktivitas:</strong> Melalui penulisan notasi, siswa mampu menuliskan notasi simbolik bilangan cacah 21-50 secara benar.</p>'
                ],
                [
                    'tipe_tahapan' => 'amati', 
                    'konten_tahapan' => '<p>Guru mendemonstrasikan cara membentuk bilangan:<br>2 puluhan = 20<br>3 satuan = 3<br>20 + 3 = 23</p>'
                ],
            ]),
            'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('questions')->insert([
            $formatQuestion([
                'activity_id' => $act1_3,
                'question_text' => '<p><strong>MENJODOHKAN</strong><br>Tarik garis untuk menjodohkan banyak puluhan dan satuan di sebelah kiri dengan bilangan yang sesuai di sebelah kanan!</p>',
                'answer_format' => 'matching',
                'difficulty' => 'medium',
                'options' => json_encode([
                    ['teks_pilihan' => '5 puluhan 0 satuan', 'matching_right' => '50'],
                    ['teks_pilihan' => '3 puluhan 0 satuan', 'matching_right' => '30'],
                    ['teks_pilihan' => '4 puluhan 2 satuan', 'matching_right' => '42'],
                    ['teks_pilihan' => '4 puluhan 3 satuan', 'matching_right' => '43'],
                    ['teks_pilihan' => '2 puluhan 4 satuan', 'matching_right' => '24'],
                    ['teks_pilihan' => '3 puluhan 4 satuan', 'matching_right' => '34'],
                    ['teks_pilihan' => '3 puluhan 5 satuan', 'matching_right' => '35'],
                    ['teks_pilihan' => '4 puluhan 6 satuan', 'matching_right' => '46'],
                    ['teks_pilihan' => '2 puluhan 1 satuan', 'matching_right' => '21'],
                ]),
            ])
        ]);

        // ==========================================
        // 4. AKTIVITAS 2.1: Menentukan Bilangan dari Gambar
        // ==========================================
        $act2_1 = DB::table('activities')->insertGetId([
            'module_id' => $moduleId,
            'title' => 'Aktivitas 2.1 Menentukan Bilangan dari Gambar Puluhan dan Satuan',
            'stage_type' => 'materi',
            'assessment_metrics' => json_encode([
                'numerasi' => ['ML', 'MP'], 
                'fase' => ['Pik'], 
                'steam' => ['A']
            ]),
            'stages' => json_encode([
                [
                    'tipe_tahapan' => 'materi', 
                    'konten_tahapan' => '<p><strong>Tujuan Aktivitas:</strong> Siswa mampu menentukan bilangan cacah 21–50 dari representasi piktorial berupa wadah puluhan dan benda di luar wadah adalah satuan.</p>'
                ],
                [
                    'tipe_tahapan' => 'berpikir', 
                    'konten_tahapan' => '<p><strong>Pertanyaan Pemantik:</strong><br>1. Ada berapa benda satuan yang tidak dikelompokkan?<br>2. Di mana kalian pernah melihat benda yang dikelompokkan menjadi 10 dalam kehidupan sehari-hari?</p>'
                ],
            ]),
            'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('questions')->insert([
            $formatQuestion([
                'activity_id' => $act2_1,
                'question_text' => '<p>Tuliskan lambang bilangan yang dibentuk oleh <strong>2 wadah penuh (20) dan 1 bola di luar wadah (1).</strong></p>',
                'answer_format' => 'number_input',
                'difficulty' => 'easy',
                'correct_answer' => '21',
            ]),
            $formatQuestion([
                'activity_id' => $act2_1,
                'question_text' => '<p>Tuliskan lambang bilangan yang dibentuk oleh <strong>3 wadah penuh (30) dan 0 bola di luar wadah.</strong></p>',
                'answer_format' => 'number_input',
                'difficulty' => 'easy',
                'correct_answer' => '30',
            ]),
        ]);

    }
}