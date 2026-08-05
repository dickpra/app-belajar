<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete(); // Hanya relasi ke Activity
            $table->longText('question_text');
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->string('image')->nullable();
            $table->string('sign_language_video')->nullable();
            $table->string('answer_format'); // multiple_choice, number_input, text
            $table->string('correct_answer')->nullable();
            $table->text('answer_explanation')->nullable();
            $table->string('layout_position')->default('image_left');
            $table->jsonb('options')->nullable(); // Gunakan jsonb untuk optimasi PostgreSQL
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
