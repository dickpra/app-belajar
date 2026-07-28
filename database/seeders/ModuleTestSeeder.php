<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Activity;
use App\Models\Question;

class ModuleTestSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // MODUL 1: Pengukuran Massa Benda (Tanpa PIN)
        // ==========================================
        $modul1 = Module::create([
            'title' => 'Modul Minggu ke-8: Pengukuran Massa Benda',
            'description' => 'Mari belajar membandingkan dan mengukur berat benda di sekitar kita!',
            'access_pin' => null, // Bebas akses tanpa PIN
            'is_active' => true,
        ]);

        // --- AKTIVITAS 1.1 ---
        $act1 = Activity::create([
            'module_id' => $modul1->id,
            'title' => 'Aktivitas 1: Mana yang Lebih Berat?',
            'description' => '<p>Perhatikan gambar timbangan di bawah ini dengan saksama, lalu jawab rentetan pertanyaannya ya.</p>',
            // 'image' => null, (Sengaja dikosongkan agar Anda bisa coba upload dari admin nanti)
        ]);

        // Soal 1 (Pilihan Ganda, Image di Kiri)
        Question::create([
            'activity_id' => $act1->id,
            'question_text' => '<p>Berdasarkan pengamatanmu, benda manakah yang lebih <strong>berat</strong>?</p>',
            'answer_format' => 'multiple_choice',
            'layout_position' => 'image_left',
            'options' => [
                ['teks_pilihan' => 'Buku Cetak', 'is_correct' => true],
                ['teks_pilihan' => 'Penghapus', 'is_correct' => false],
            ],
        ]);

        // Soal 2 (Pilihan Ganda, Image di Atas)
        Question::create([
            'activity_id' => $act1->id,
            'question_text' => '<p>Simbol matematika yang tepat untuk membandingkan <strong>Buku ... Penghapus</strong> adalah?</p>',
            'answer_format' => 'multiple_choice',
            'layout_position' => 'image_top',
            'options' => [
                ['teks_pilihan' => '>', 'is_correct' => true],
                ['teks_pilihan' => '<', 'is_correct' => false],
                ['teks_pilihan' => '=', 'is_correct' => false],
            ],
        ]);

        // --- AKTIVITAS 1.2 ---
        $act2 = Activity::create([
            'module_id' => $modul1->id,
            'title' => 'Aktivitas 2: Mengukur dengan Kelereng',
            'description' => '<p>Sekarang kita akan menggunakan kelereng sebagai alat ukur tidak baku.</p>',
        ]);

        // Soal 3 (Input Angka, Image di Kanan)
        Question::create([
            'activity_id' => $act2->id,
            'question_text' => '<p>Coba perkirakan, berapa <strong>butir kelereng</strong> yang dibutuhkan agar timbangan seimbang dengan 1 buah Apel?</p>',
            'answer_format' => 'number_input',
            'layout_position' => 'image_right',
        ]);

        // Soal 4 (Input Teks Singkat, Image di Bawah)
        Question::create([
            'activity_id' => $act2->id,
            'question_text' => '<p>Isi bagian yang rumpang: Massa benda dapat diukur menggunakan satuan ukur yang ... (Ketik: <em>Baku</em> atau <em>Tidak Baku</em>)</p>',
            'answer_format' => 'text_input',
            'layout_position' => 'image_bottom',
        ]);


        // ==========================================
        // MODUL 2: Tantangan Terkunci (Pakai PIN)
        // ==========================================
        $modul2 = Module::create([
            'title' => 'Tantangan Rahasia: Timbangan Digital',
            'description' => 'Modul ini dikunci oleh gurumu. Masukkan PIN untuk memulai eksperimen!',
            'access_pin' => '1234', // Menggunakan PIN: 1234
            'is_active' => true,
        ]);

        // --- AKTIVITAS 2.1 ---
        $act3 = Activity::create([
            'module_id' => $modul2->id,
            'title' => 'Praktik Menimbang',
            'description' => '<p>Nyalakan timbangan digitalmu, pastikan angkanya di posisi <strong>0</strong> ya.</p>',
        ]);

        // Soal 1 Modul 2
        Question::create([
            'activity_id' => $act3->id,
            'question_text' => '<p>Berapa gram berat tempat pensil milikmu setelah ditimbang?</p>',
            'answer_format' => 'number_input',
            'layout_position' => 'image_left',
        ]);
    }
}