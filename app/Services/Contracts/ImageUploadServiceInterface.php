<?php

namespace App\Services\Contracts;

use Illuminate\Http\UploadedFile;

interface ImageUploadServiceInterface
{
    /**
     * @return array{url: string, public_id: string}
     */
    public function upload(UploadedFile $file): array;

    public function delete(string $publicId): bool;
}
