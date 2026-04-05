<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class AudioFileController extends Controller
{
    public function serve(string $path): mixed
    {
        $fullPath = storage_path('app/public/' . $path);
        
        // Verify the file exists
        if (!file_exists($fullPath)) {
            abort(404, 'Audio file not found');
        }
        
        // Verify it's in the conversations directory
        $realPath = realpath($fullPath);
        $allowedPath = realpath(storage_path('app/public/conversations'));
        
        if ($realPath === false || strpos($realPath, $allowedPath) !== 0) {
            abort(403, 'Access denied');
        }
        
        // Determine MIME type
        $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'webm' => 'audio/webm',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'm4a' => 'audio/mp4',
            'ogg' => 'audio/ogg',
        ];
        
        $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
        
        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
