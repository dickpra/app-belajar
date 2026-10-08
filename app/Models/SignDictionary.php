<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SignDictionary extends Model
{
    protected $fillable = ['word', 'video_path'];
}