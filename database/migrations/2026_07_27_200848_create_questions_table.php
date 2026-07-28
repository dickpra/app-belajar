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
            $table->text('question_text');
            $table->string('image')->nullable();
            $table->string('answer_format'); // multiple_choice, number_input, text
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
