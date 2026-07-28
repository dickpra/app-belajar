<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Ini kunci utama agar kolom JSON bisa langsung digunakan oleh Filament
    protected $casts = [
        'options' => 'array',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    // Relasi ke atas: Milik 1 Aktivitas
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}