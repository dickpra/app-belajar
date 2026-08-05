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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->string('title'); // Menggantikan 'activity_title' yang tersesat di questions
            $table->string('stage_type')->default('materi'); // Materi, Praktek, Pembelajaran
            $table->longText('description')->nullable(); // Teks penjelasan/instruksi
            $table->json('stages')->nullable();
            $table->json('assessment_metrics')->nullable();
            $table->string('image')->nullable();     // Gambar utama
            $table->string('sign_language_video')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
