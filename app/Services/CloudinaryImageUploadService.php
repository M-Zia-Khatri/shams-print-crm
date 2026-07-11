<?php

namespace App\Services;

use App\Exceptions\ImageUploadException;
use App\Services\Contracts\ImageUploadServiceInterface;
use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Throwable;

class CloudinaryImageUploadService implements ImageUploadServiceInterface
{
    /**
     * @return array{url: string, public_id: string, secure_url: string, width: int|null, height: int|null, bytes: int|null, format: string|null, resource_type: string|null}
     */
    public function upload(UploadedFile $file): array
    {
        try {
            $response = $this->cloudinary()->uploadApi()->upload($file->getRealPath(), [
                'folder' => 'item_entries',
                'resource_type' => 'image',
            ]);
        } catch (ImageUploadException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw ImageUploadException::uploadFailed($exception);
        }

        $secureUrl = (string) ($response['secure_url'] ?? $response['url'] ?? '');

        return [
            'url' => $secureUrl,
            'public_id' => (string) ($response['public_id'] ?? ''),
            'secure_url' => $secureUrl,
            'width' => isset($response['width']) ? (int) $response['width'] : null,
            'height' => isset($response['height']) ? (int) $response['height'] : null,
            'bytes' => isset($response['bytes']) ? (int) $response['bytes'] : null,
            'format' => isset($response['format']) ? (string) $response['format'] : null,
            'resource_type' => isset($response['resource_type']) ? (string) $response['resource_type'] : null,
        ];
    }

    public function delete(string $publicId): bool
    {
        if ($publicId === '') {
            return false;
        }

        try {
            $response = $this->cloudinary()->uploadApi()->destroy($publicId, [
                'resource_type' => 'image',
            ]);
        } catch (ImageUploadException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw ImageUploadException::deleteFailed($exception);
        }

        return in_array($response['result'] ?? null, ['ok', 'not found'], true);
    }

    private function cloudinary(): Cloudinary
    {
        if (! class_exists(Cloudinary::class)) {
            throw ImageUploadException::packageMissing();
        }

        $cloudinaryUrl = config('cloudinary.cloudinary_url');

        if (! is_string($cloudinaryUrl) || $cloudinaryUrl === '') {
            throw ImageUploadException::missingConfiguration();
        }

        return new Cloudinary($cloudinaryUrl);
    }
}
