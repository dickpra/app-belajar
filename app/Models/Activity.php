<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = ['stages' => 'array', 'assessment_metrics' => 'array'];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    // Relasi ke bawah: Memiliki Banyak Soal
    public function questions()
    {
        return $this->hasMany(Question::class, 'activity_id');
    }
}
