<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('sign_dictionaries', function (Blueprint $table) {
        $table->id();
        $table->string('word')->unique(); // Kata (Misal: "Buku")
        $table->string('video_path');     // Lokasi file video
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sign_dictionaries');
    }
};
