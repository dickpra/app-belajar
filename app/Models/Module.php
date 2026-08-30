<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relasi langsung menembus Aktivitas untuk mengambil Soal
    public function questions()
    {
        return $this->hasManyThrough(Question::class, Activity::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
    
}