<?php

namespace App\Services;

use App\Services\Contracts\ImageUploadServiceInterface;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class CloudinaryImageUploadService implements ImageUploadServiceInterface
{
    /**
     * @return array{url: string, public_id: string}
     */
    public function upload(UploadedFile $file): array
    {
        $this->ensureCloudinaryIsInstalled();

        $response = app('cloudinary')->uploadApi()->upload($file->getRealPath(), [
            'folder' => 'item_entries',
            'resource_type' => 'image',
        ]);

        return [
            'url' => (string) ($response['secure_url'] ?? $response['url'] ?? ''),
            'public_id' => (string) ($response['public_id'] ?? ''),
        ];
    }

    public function delete(string $publicId): bool
    {
        $this->ensureCloudinaryIsInstalled();

        if ($publicId === '') {
            return false;
        }

        $response = app('cloudinary')->uploadApi()->destroy($publicId, [
            'resource_type' => 'image',
        ]);

        return ($response['result'] ?? null) === 'ok' || ($response['result'] ?? null) === 'not found';
    }

    private function ensureCloudinaryIsInstalled(): void
    {
        if (! class_exists('Cloudinary\\Laravel\\CloudinaryEngine') && ! class_exists('CloudinaryLabs\\CloudinaryLaravel\\CloudinaryEngine')) {
            throw new RuntimeException('The cloudinary-labs/cloudinary-laravel package is not installed yet. Run composer install after network access is restored.');
        }
    }
}
