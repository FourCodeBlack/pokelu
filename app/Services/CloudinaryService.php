<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    protected $cloudName = 'dsz8bojjy';
    protected $uploadPreset = 'pokelu_storage';

    public function uploadImage(UploadedFile $file, $folder = 'pokelu/forum')
    {
        $response = Http::attach(
            'file',
            file_get_contents($file->getRealPath()),
            $file->getClientOriginalName()
        )->post("https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload", [
            'upload_preset' => $this->uploadPreset,
            'folder' => $folder,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Cloudinary upload failed: ' . $response->body());
    }
}
