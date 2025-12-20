<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

/**
 * Image Upload Service.
 *
 * Handles image uploads with optimization, resizing, and multiple file support.
 *
 * @requirement PROD-007 Implement product image upload
 * @requirement PROD-013 Multi-image upload with drag-drop
 */
class ImageUploadService
{
    /**
     * Maximum number of images per product.
     */
    public const MAX_IMAGES = 5;

    /**
     * Maximum file size in kilobytes.
     */
    public const MAX_SIZE_KB = 2048;

    /**
     * Allowed MIME types.
     */
    public const ALLOWED_MIMES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

    /**
     * Image sizes for different use cases.
     */
    public const SIZES = [
        'thumbnail' => ['width' => 150, 'height' => 150],
        'medium' => ['width' => 500, 'height' => 500],
        'large' => ['width' => 1200, 'height' => 1200],
    ];

    /**
     * Upload and process a single image.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $directory  Storage directory (e.g., 'products', 'categories')
     * @param  bool  $generateThumbnails  Generate thumbnail and medium sizes
     * @return array{original: string, thumbnail: ?string, medium: ?string}
     * @throws \Exception
     */
    public function uploadImage(
        UploadedFile $file,
        string $directory = 'products',
        bool $generateThumbnails = true
    ): array {
        // Validate file
        $this->validateImage($file);

        // Generate unique filename
        $filename = $this->generateFilename($file);

        // Define storage paths
        $paths = [
            'original' => "{$directory}/{$filename}",
            'thumbnail' => $generateThumbnails ? "{$directory}/thumbnails/{$filename}" : null,
            'medium' => $generateThumbnails ? "{$directory}/medium/{$filename}" : null,
        ];

        try {
            // Store original image
            $file->storeAs("public/{$directory}", $filename);

            // Generate thumbnails if requested
            if ($generateThumbnails) {
                $this->generateThumbnail($file, $directory, $filename, 'thumbnail');
                $this->generateThumbnail($file, $directory, $filename, 'medium');
            }

            return [
                'original' => Storage::url($paths['original']),
                'thumbnail' => $paths['thumbnail'] ? Storage::url($paths['thumbnail']) : null,
                'medium' => $paths['medium'] ? Storage::url($paths['medium']) : null,
            ];
        } catch (\Exception $e) {
            // Clean up any uploaded files on error
            $this->deleteImage($paths['original']);
            if ($paths['thumbnail']) {
                $this->deleteImage($paths['thumbnail']);
            }
            if ($paths['medium']) {
                $this->deleteImage($paths['medium']);
            }

            throw new \Exception("Failed to upload image: " . $e->getMessage());
        }
    }

    /**
     * Upload multiple images.
     *
     * @param  array<\Illuminate\Http\UploadedFile>  $files
     * @param  string  $directory
     * @param  bool  $generateThumbnails
     * @return array<array{original: string, thumbnail: ?string, medium: ?string}>
     * @throws \Exception
     */
    public function uploadMultiple(
        array $files,
        string $directory = 'products',
        bool $generateThumbnails = true
    ): array {
        // Validate total count
        if (count($files) > self::MAX_IMAGES) {
            throw new \Exception("Maximum " . self::MAX_IMAGES . " images allowed.");
        }

        $uploaded = [];

        try {
            foreach ($files as $file) {
                $uploaded[] = $this->uploadImage($file, $directory, $generateThumbnails);
            }

            return $uploaded;
        } catch (\Exception $e) {
            // Clean up all uploaded files on error
            foreach ($uploaded as $paths) {
                $this->deleteImage($paths['original']);
                if ($paths['thumbnail']) {
                    $this->deleteImage($paths['thumbnail']);
                }
                if ($paths['medium']) {
                    $this->deleteImage($paths['medium']);
                }
            }

            throw $e;
        }
    }

    /**
     * Delete an image and its thumbnails.
     *
     * @param  string  $path  Image path (can be URL or storage path)
     * @return bool
     */
    public function deleteImage(string $path): bool
    {
        // Convert URL to storage path if necessary
        $storagePath = str_replace('/storage/', '', parse_url($path, PHP_URL_PATH));

        try {
            // Delete original
            Storage::disk('public')->delete($storagePath);

            // Try to delete thumbnails
            $pathInfo = pathinfo($storagePath);
            $directory = $pathInfo['dirname'];
            $filename = $pathInfo['basename'];

            Storage::disk('public')->delete("{$directory}/thumbnails/{$filename}");
            Storage::disk('public')->delete("{$directory}/medium/{$filename}");

            return true;
        } catch (\Exception $e) {
            // Log error but don't throw
            \Log::error("Failed to delete image: {$path}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Validate image file.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @throws \Exception
     */
    protected function validateImage(UploadedFile $file): void
    {
        // Check if file is valid
        if (!$file->isValid()) {
            throw new \Exception("Invalid file upload.");
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), self::ALLOWED_MIMES, true)) {
            throw new \Exception("File must be JPEG, JPG, PNG, or WEBP format.");
        }

        // Check file size
        if ($file->getSize() > self::MAX_SIZE_KB * 1024) {
            throw new \Exception("File size must not exceed " . self::MAX_SIZE_KB . "KB (2MB).");
        }

        // Check if it's actually an image
        $imageInfo = @getimagesize($file->getRealPath());
        if ($imageInfo === false) {
            throw new \Exception("File is not a valid image.");
        }
    }

    /**
     * Generate filename with unique identifier.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return string
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $unique = Str::random(8);
        $timestamp = time();

        return "{$name}-{$timestamp}-{$unique}.{$extension}";
    }

    /**
     * Generate thumbnail version of image.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $directory
     * @param  string  $filename
     * @param  string  $size  'thumbnail' or 'medium'
     */
    protected function generateThumbnail(
        UploadedFile $file,
        string $directory,
        string $filename,
        string $size
    ): void {
        // Get size dimensions
        $dimensions = self::SIZES[$size] ?? self::SIZES['thumbnail'];

        // Create directory if it doesn't exist
        $path = storage_path("app/public/{$directory}/{$size}");
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        // Read image and resize
        $image = \Image::make($file->getRealPath());

        // Resize maintaining aspect ratio
        $image->resize($dimensions['width'], $dimensions['height'], function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        // Save optimized image
        $image->save("{$path}/{$filename}", 85); // 85% quality
    }

    /**
     * Reorder images based on provided order.
     *
     * @param  array<string>  $imagePaths  Array of image paths in desired order
     * @return array<string>  Reordered paths
     */
    public function reorderImages(array $imagePaths): array
    {
        // Simply return the array as provided
        // This method exists for future implementation if we need database updates
        return array_values($imagePaths);
    }

    /**
     * Get image dimensions.
     *
     * @param  string  $path
     * @return array{width: int, height: int}|null
     */
    public function getImageDimensions(string $path): ?array
    {
        $storagePath = storage_path('app/public/' . str_replace('/storage/', '', $path));

        if (!file_exists($storagePath)) {
            return null;
        }

        $imageInfo = @getimagesize($storagePath);

        if ($imageInfo === false) {
            return null;
        }

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
        ];
    }
}
