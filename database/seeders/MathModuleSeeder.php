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
        // 0. INSERT MODUL UTAMA
        // ==========================================
        $moduleId = DB::table('modules')->insertGetId([
            'title' => 'Topik: Bilangan (Minggu 3)',
            'description' => 'Menyusun bilangan cacah 21-50 sebagai kombinasi puluhan dan satuan',
            'is_active' => true,
            'is_adaptive' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Fungsi bantuan untuk menstandarkan kolom pertanyaan
        // Agar tidak ada lagi error "Column count doesn't match"
        $formatQuestion = function ($data) use ($now) {
            return array_merge([
                'correct_answer' => null,
                'true_false_answer' => null,
                'correction_text' => null,
                'layout_position' => 'image_top',
                'options' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ], $data);
        };

        // ==========================================
        // 1. AKTIVITAS 1.1
        // ==========================================
        $act1_1 = DB::table('activities')->insertGetId([
            'module_id' => $moduleId,
            'title' => 'Aktivitas 1.1 Mengelompokkan Benda ke dalam Wadah Berkapasitas 10',
            'stage_type' => 'materi',
            'assessment_metrics' => json_encode(['numerasi' => ['ML', 'CeT'], 'fase' => ['Kon', 'Pik'], 'steam' => ['M']]),
            'stages' => json_encode([
                ['tipe_tahapan' => 'materi', 'konten_tahapan' => '<p><strong>Tujuan:</strong> Melalui penggunaan benda konkret, siswa mampu membentuk kelompok puluhan dengan cara mengelompokkan benda ke dalam wadah yang masing-masing berisi 10 benda.</p>'],
                ['tipe_tahapan' => 'amati', 'konten_tahapan' => '<p>Amati setiap kelompok benda dengan saksama. Setiap kelompok terdiri atas 10 benda... 10 benda yang dikelompokkan menjadi satu disebut 1 puluhan.</p>'],
            ]),
            'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('questions')->insert([
            $formatQuestion([
                'activity_id' => $act1_1,
                'question_text' => '<p>Berapa banyak bola semuanya?</p>',
                'answer_format' => 'number_input',
                'difficulty' => 'easy',
                'correct_answer' => '24',
            ]),
            $formatQuestion([
                'activity_id' => $act1_1,
                'question_text' => '<p>Bilangan yang terbentuk adalah ________</p>',
                'answer_format' => 'number_input',
                'difficulty' => 'easy',
                'correct_answer' => '32',
            ]),
            $formatQuestion([
                'activity_id' => $act1_1,
                'question_text' => '<p>Berapa banyak kelereng semuanya? ________</p>',
                'answer_format' => 'number_input',
                'difficulty' => 'easy',
                'correct_answer' => '40',
            ]),
        ]);

        // ==========================================
        // 2. AKTIVITAS 1.2
        // ==========================================
        $act1_2 = DB::table('activities')->insertGetId([
            'module_id' => $moduleId,
            'title' => 'Aktivitas 1.2. Menentukan banyak puluhan dan satuan dari hasil pengelompokan',
            'stage_type' => 'materi',
            'assessment_metrics' => json_encode(['numerasi' => ['ML', 'PS', 'CeT'], 'fase' => ['Kon', 'Pik'], 'steam' => []]),
            'stages' => json_encode([
                ['tipe_tahapan' => 'materi', 'konten_tahapan' => '<p>Puluhan sebagai Satu Unit: Sepuluh satuan dapat digabungkan menjadi satu puluhan...</p>'],
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
                'question_text' => '<p>Budi memiliki 4 kantong kelereng. Setiap kantong berisi 10 kelereng. Masih ada 5 kelereng di luar kantong.<br><strong>Berapa jumlah seluruh kelereng Budi?</strong></p>',
                'answer_format' => 'number_input',
                'difficulty' => 'medium',
                'correct_answer' => '45',
            ]),
        ]);

        // ==========================================
        // 3. AKTIVITAS 1.3
        // ==========================================
        $act1_3 = DB::table('activities')->insertGetId([
            'module_id' => $moduleId,
            'title' => 'Aktivitas 1.3. Menggabungkan puluhan dan satuan menjadi bilangan cacah, lalu menuliskannya',
            'stage_type' => 'materi',
            'assessment_metrics' => json_encode(['numerasi' => ['ML', 'MP', 'CeT'], 'fase' => ['Pik'], 'steam' => []]),
            'stages' => json_encode([
                ['tipe_tahapan' => 'amati', 'konten_tahapan' => '<p>Guru mendemonstrasikan cara membentuk bilangan: 2 puluhan = 20, 3 satuan = 3, 20 + 3 = 23.</p>'],
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
                    ['teks_pilihan' => '4 puluhan 0 satuan', 'matching_right' => '40'],
                    ['teks_pilihan' => '2 puluhan 4 satuan', 'matching_right' => '24'],
                    ['teks_pilihan' => '3 puluhan 4 satuan', 'matching_right' => '34'],
                    ['teks_pilihan' => '3 puluhan 5 satuan', 'matching_right' => '35'],
                    ['teks_pilihan' => '4 puluhan 6 satuan', 'matching_right' => '46'],
                    ['teks_pilihan' => '2 puluhan 1 satuan', 'matching_right' => '21'],
                ]),
            ])
        ]);

        // ==========================================
        // 4. AKTIVITAS 2.1
        // ==========================================
        $act2_1 = DB::table('activities')->insertGetId([
            'module_id' => $moduleId,
            'title' => 'Aktivitas 2.1 Menentukan Bilangan dari Gambar Puluhan dan Satuan',
            'stage_type' => 'materi',
            'assessment_metrics' => json_encode(['numerasi' => ['ML', 'MP'], 'fase' => ['Pik'], 'steam' => ['A']]),
            'stages' => json_encode([
                ['tipe_tahapan' => 'materi', 'konten_tahapan' => '<p>Siswa menentukan bilangan cacah 21–50 dari representasi piktorial berupa wadah puluhan dan benda di luar wadah adalah satuan.</p>'],
            ]),
            'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('questions')->insert([
            $formatQuestion([
                'activity_id' => $act2_1,
                'question_text' => '<p>Rena berkata: "Ada 32 buah stroberi." Budi berkata: "Bukan, ada 23 buah stroberi."<br><strong>Siapa yang benar?</strong></p>',
                'answer_format' => 'text_input',
                'difficulty' => 'medium',
                'correct_answer' => 'Budi',
            ]),
            $formatQuestion([
                'activity_id' => $act2_1,
                'question_text' => '<p>Bagaimana kamu tahu? Tuliskan jawaban yang benar: <strong>_____ puluhan + _____ satuan = _____</strong></p>',
                'answer_format' => 'complex_fill',
                'difficulty' => 'medium',
                'options' => json_encode([
                    ['teks_pilihan' => '2'],
                    ['teks_pilihan' => '3'],
                    ['teks_pilihan' => '23']
                ]),
            ])
        ]);

        // ==========================================
        // 5. AKTIVITAS 3.1
        // ==========================================
        $act3_1 = DB::table('activities')->insertGetId([
            'module_id' => $moduleId,
            'title' => '3.1 Menentukan Banyak Puluhan dan Satuan dari Bilangan 21–50 yang Diberikan',
            'stage_type' => 'materi',
            'assessment_metrics' => json_encode(['numerasi' => ['ML', 'MP', 'CiT'], 'fase' => ['Abs'], 'steam' => ['M']]),
            'stages' => json_encode([
                ['tipe_tahapan' => 'materi', 'konten_tahapan' => '<p>Pada bilangan dua angka, angka pertama menunjukkan banyak puluhan dan angka kedua menunjukkan banyak satuan.</p>'],
            ]),
            'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('questions')->insert([
            $formatQuestion([
                'activity_id' => $act3_1,
                'question_text' => '<p>1. a. Bilangan 37 terdiri atas ...</p>',
                'answer_format' => 'multiple_choice',
                'difficulty' => 'easy',
                'options' => json_encode([
                    ['teks_pilihan' => '3 puluhan dan 7 satuan', 'is_correct' => true],
                    ['teks_pilihan' => '7 puluhan dan 3 satuan', 'is_correct' => false],
                    ['teks_pilihan' => '2 puluhan dan 7 satuan', 'is_correct' => false],
                ]),
            ]),
            $formatQuestion([
                'activity_id' => $act3_1,
                'question_text' => '<p>1. b. Bilangan 42 terdiri atas…</p>',
                'answer_format' => 'multiple_choice',
                'difficulty' => 'easy',
                'options' => json_encode([
                    ['teks_pilihan' => '4 puluhan dan 2 satuan', 'is_correct' => true],
                    ['teks_pilihan' => '2 puluhan dan 4 satuan', 'is_correct' => false],
                    ['teks_pilihan' => '4 puluhan dan 4 satuan', 'is_correct' => false],
                ]),
            ]),
            $formatQuestion([
                'activity_id' => $act3_1,
                'question_text' => '<p>1. c. Bilangan 50 terdiri atas …</p>',
                'answer_format' => 'multiple_choice',
                'difficulty' => 'easy',
                'options' => json_encode([
                    ['teks_pilihan' => '5 puluhan dan 0 satuan', 'is_correct' => true],
                    ['teks_pilihan' => '0 puluhan dan 5 satuan', 'is_correct' => false],
                    ['teks_pilihan' => '5 puluhan dan 5 satuan', 'is_correct' => false],
                ]),
            ]),
            $formatQuestion([
                'activity_id' => $act3_1,
                'question_text' => '<p>2. a. Rina mengatakan bahwa 38 terdiri atas 8 puluhan dan 3 satuan.<br>Apakah Rina benar?</p>',
                'answer_format' => 'true_false_correction',
                'difficulty' => 'medium',
                'true_false_answer' => 'Salah',
                'correction_text' => '3 puluhan dan 8 satuan',
            ]),
            $formatQuestion([
                'activity_id' => $act3_1,
                'question_text' => '<p>2. b. Budi mengatakan bahwa 45 terdiri atas 4 puluhan dan 5 satuan.<br>Apakah Budi benar?</p>',
                'answer_format' => 'true_false_correction',
                'difficulty' => 'easy',
                'true_false_answer' => 'Benar',
            ]),
            $formatQuestion([
                'activity_id' => $act3_1,
                'question_text' => '<p>2. c. Siti mengatakan bahwa 50 terdiri atas 5 puluhan dan 5 satuan.<br>Apakah Siti benar?</p>',
                'answer_format' => 'true_false_correction',
                'difficulty' => 'medium',
                'true_false_answer' => 'Salah',
                'correction_text' => '5 puluhan dan 0 satuan',
            ]),
        ]);

        // ==========================================
        // 6. AKTIVITAS 3.2
        // ==========================================
        $act3_2 = DB::table('activities')->insertGetId([
            'module_id' => $moduleId,
            'title' => '3.2 Menuliskan Notasi Simbolik Bilangan',
            'stage_type' => 'materi',
            'assessment_metrics' => json_encode(['numerasi' => ['ML', 'MP', 'CiT'], 'fase' => ['Abs'], 'steam' => ['A', 'E']]),
            'stages' => json_encode([
                ['tipe_tahapan' => 'materi', 'konten_tahapan' => '<p>Guru menjelaskan bahwa notasi simbolik bilangan ditulis dengan menempatkan angka puluhan di depan dan angka satuan di belakang.</p>'],
            ]),
            'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('questions')->insert([
            $formatQuestion([
                'activity_id' => $act3_2,
                'question_text' => '<p>1. Lengkapi.<br>a. 37 = ____ + ____</p>',
                'answer_format' => 'complex_fill',
                'difficulty' => 'easy',
                'options' => json_encode([['teks_pilihan' => '30'], ['teks_pilihan' => '7']]),
            ]),
            $formatQuestion([
                'activity_id' => $act3_2,
                'question_text' => '<p>1. Lengkapi.<br>b. 42 = ____ + ____</p>',
                'answer_format' => 'complex_fill',
                'difficulty' => 'easy',
                'options' => json_encode([['teks_pilihan' => '40'], ['teks_pilihan' => '2']]),
            ]),
            $formatQuestion([
                'activity_id' => $act3_2,
                'question_text' => '<p>1. Lengkapi.<br>c. 28 = ____ + ____</p>',
                'answer_format' => 'complex_fill',
                'difficulty' => 'easy',
                'options' => json_encode([['teks_pilihan' => '20'], ['teks_pilihan' => '8']]),
            ]),
            $formatQuestion([
                'activity_id' => $act3_2,
                'question_text' => '<p>2. Temukan Kesalahan.<br>a. 34 = 3 + 4</p>',
                'answer_format' => 'true_false_correction',
                'difficulty' => 'medium',
                'true_false_answer' => 'Salah',
                'correction_text' => '30 + 4',
            ]),
            $formatQuestion([
                'activity_id' => $act3_2,
                'question_text' => '<p>2. Temukan Kesalahan.<br>b. 46 = 40 + 6</p>',
                'answer_format' => 'true_false_correction',
                'difficulty' => 'easy',
                'true_false_answer' => 'Benar',
            ]),
            $formatQuestion([
                'activity_id' => $act3_2,
                'question_text' => '<p>2. Temukan Kesalahan.<br>c. 50 = 5 + 0</p>',
                'answer_format' => 'true_false_correction',
                'difficulty' => 'medium',
                'true_false_answer' => 'Salah',
                'correction_text' => '50 + 0',
            ]),
            $formatQuestion([
                'activity_id' => $act3_2,
                'question_text' => '<p>3. Perhatikan kartu bilangan 36.<br>Dua teman menyusun bilangan di atas dengan cara berbeda:<br>Cara 1: Isi 3 wadah penuh dulu, baru letakkan 6 benda di luar.<br>Cara 2: Letakkan 6 benda di luar, baru isi 3 wadah penuh.<br><strong>Apakah menghasilkan bilangan yang sama? _____</strong></p>',
                'answer_format' => 'text_input',
                'difficulty' => 'medium',
                'correct_answer' => 'Ya',
            ]),
            $formatQuestion([
                'activity_id' => $act3_2,
                'question_text' => '<p><strong>Cara mana yang lebih mudah kamu hitung? _____</strong></p>',
                'answer_format' => 'text_input',
                'difficulty' => 'easy',
                'correct_answer' => 'Cara 1',
            ]),
        ]);

        // ==========================================
        // 7. AKTIVITAS 3.3
        // ==========================================
        $act3_3 = DB::table('activities')->insertGetId([
            'module_id' => $moduleId,
            'title' => '3.3 Menuliskan Bentuk Puluhan + Satuan',
            'stage_type' => 'materi',
            'assessment_metrics' => json_encode(['numerasi' => ['ML', 'MP'], 'fase' => ['Abs'], 'steam' => ['A']]),
            'stages' => json_encode([
                ['tipe_tahapan' => 'materi', 'konten_tahapan' => '<p>Bilangan dapat ditulis dalam bentuk puluhan dan satuan. Banyak puluhan ditulis terlebih dahulu, kemudian banyak satuan, lalu dituliskan bilangan yang terbentuk.</p>'],
            ]),
            'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('questions')->insert([
            $formatQuestion([
                'activity_id' => $act3_3,
                'question_text' => '<p>1. Lengkapi.<br>a. _____ puluhan + _____ satuan = 37</p>',
                'answer_format' => 'complex_fill',
                'difficulty' => 'easy',
                'options' => json_encode([['teks_pilihan' => '3'], ['teks_pilihan' => '7']]),
            ]),
            $formatQuestion([
                'activity_id' => $act3_3,
                'question_text' => '<p>1. Lengkapi.<br>b. _____ puluhan + _____ satuan = 42</p>',
                'answer_format' => 'complex_fill',
                'difficulty' => 'easy',
                'options' => json_encode([['teks_pilihan' => '4'], ['teks_pilihan' => '2']]),
            ]),
            $formatQuestion([
                'activity_id' => $act3_3,
                'question_text' => '<p>1. Lengkapi.<br>c. _____ puluhan + _____ satuan = 28</p>',
                'answer_format' => 'complex_fill',
                'difficulty' => 'easy',
                'options' => json_encode([['teks_pilihan' => '2'], ['teks_pilihan' => '8']]),
            ]),
            $formatQuestion([
                'activity_id' => $act3_3,
                'question_text' => '<p>2. Periksa dan perbaiki jika salah.<br>a. 3 puluhan + 8 satuan = 83</p>',
                'answer_format' => 'true_false_correction',
                'difficulty' => 'medium',
                'true_false_answer' => 'Salah',
                'correction_text' => '38',
            ]),
            $formatQuestion([
                'activity_id' => $act3_3,
                'question_text' => '<p>2. Periksa dan perbaiki jika salah.<br>b. 4 puluhan + 6 satuan = 46</p>',
                'answer_format' => 'true_false_correction',
                'difficulty' => 'easy',
                'true_false_answer' => 'Benar',
            ]),
            $formatQuestion([
                'activity_id' => $act3_3,
                'question_text' => '<p>2. Periksa dan perbaiki jika salah.<br>c. 5 puluhan + 0 satuan = 05</p>',
                'answer_format' => 'true_false_correction',
                'difficulty' => 'medium',
                'true_false_answer' => 'Salah',
                'correction_text' => '50',
            ]),
            $formatQuestion([
                'activity_id' => $act3_3,
                'question_text' => '<p>3. Bu Guru minta tolong. Ia punya 43 buku yang akan dimasukkan ke dalam kotak. Setiap kotak muat 10 buku.<br><strong>Berapa kotak yang terisi penuh? _____</strong></p>',
                'answer_format' => 'number_input',
                'difficulty' => 'medium',
                'correct_answer' => '4',
            ]),
            $formatQuestion([
                'activity_id' => $act3_3,
                'question_text' => '<p><strong>Berapa buku yang tidak masuk ke kotak? _____</strong></p>',
                'answer_format' => 'number_input',
                'difficulty' => 'medium',
                'correct_answer' => '3',
            ]),
            $formatQuestion([
                'activity_id' => $act3_3,
                'question_text' => '<p><strong>Tuliskan dalam notasi: ____ puluhan + ____ satuan = ____</strong></p>',
                'answer_format' => 'complex_fill',
                'difficulty' => 'medium',
                'options' => json_encode([
                    ['teks_pilihan' => '4'],
                    ['teks_pilihan' => '3'],
                    ['teks_pilihan' => '43']
                ]),
            ])
        ]);
    }
}