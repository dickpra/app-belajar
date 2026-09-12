<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CKEditorUploadController extends Controller
{
    public function upload(Request $request)
    {
        // Validasi gambar yang di-paste dari Word/komputer
        $request->validate([
            'upload' => 'required|image|max:10240', // Maksimal 10MB
        ]);

        // Simpan gambar ke disk modul_rahasia Anda
        $path = $request->file('upload')->store('modul_private/ckeditor_uploads', 'modul_rahasia');

        // Kembalikan URL gambar dalam format JSON yang diminta oleh CKEditor
        return response()->json([
            'url' => Storage::disk('modul_rahasia')->url($path)
        ]);
    }
}