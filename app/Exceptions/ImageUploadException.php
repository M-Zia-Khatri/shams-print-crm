<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class ImageUploadException extends RuntimeException
{
    public static function packageMissing(): self
    {
        return new self('The cloudinary/cloudinary_php SDK is not installed yet. Run composer install after network access is restored.');
    }

    public static function missingConfiguration(): self
    {
        return new self('Cloudinary is not configured. Set CLOUDINARY_URL before running uploads.');
    }

    public static function uploadFailed(Throwable $exception): self
    {
        return new self('Cloudinary image upload failed: '.$exception->getMessage(), previous: $exception);
    }

    public static function deleteFailed(Throwable $exception): self
    {
        return new self('Cloudinary image deletion failed: '.$exception->getMessage(), previous: $exception);
    }
}
