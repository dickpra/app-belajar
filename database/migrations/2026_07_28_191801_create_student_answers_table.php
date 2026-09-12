<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->text('answer_value')->nullable(); // Jawaban murid (angka/teks/opsi)
            $table->boolean('is_correct')->nullable(); // Untuk auto-grading pilihan ganda
            $table->integer('score')->nullable();      // Untuk nilai angka (0-100)
            $table->text('teacher_notes')->nullable(); // Catatan perbaikan dari guru
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_answers');
    }
};
