<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class MediaOptimizer
{
    /**
     * @return array{contents: string, extension: string, mime: string}
     */
    public function optimizeImage(UploadedFile $file, int $maxWidth = 1080, int $quality = 82): array
    {
        $fallback = $this->originalPayload($file);

        if (!extension_loaded('gd')) {
            return $fallback;
        }

        $mime = strtolower((string) $file->getMimeType());

        if ($mime === 'image/gif') {
            return $fallback;
        }

        if (!in_array($mime, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'], true)) {
            return $fallback;
        }

        $raw = file_get_contents($file->getRealPath());
        if ($raw === false) {
            return $fallback;
        }

        $source = @imagecreatefromstring($raw);
        if ($source === false) {
            return $fallback;
        }

        $width = imagesx($source);
        $height = imagesy($source);

        if ($width < 1 || $height < 1) {
            imagedestroy($source);

            return $fallback;
        }

        $target = $source;

        if ($width > $maxWidth) {
            $newHeight = (int) round($height * ($maxWidth / $width));
            $resized = imagecreatetruecolor($maxWidth, $newHeight);

            if ($mime === 'image/png' || $mime === 'image/webp') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }

            imagecopyresampled($resized, $source, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
            imagedestroy($source);
            $target = $resized;
        }

        ob_start();

        if (function_exists('imagewebp')) {
            imagewebp($target, null, $quality);
            $extension = 'webp';
            $mimeType = 'image/webp';
        } else {
            imagejpeg($target, null, min(90, $quality + 8));
            $extension = 'jpg';
            $mimeType = 'image/jpeg';
        }

        $contents = ob_get_clean();
        imagedestroy($target);

        if ($contents === false || $contents === '') {
            return $fallback;
        }

        if (strlen($contents) >= strlen($fallback['contents'])) {
            return $fallback;
        }

        return [
            'contents' => $contents,
            'extension' => $extension,
            'mime' => $mimeType,
        ];
    }

    /**
     * @return array{contents: string, extension: string, mime: string}
     */
    private function originalPayload(UploadedFile $file): array
    {
        $contents = file_get_contents($file->getRealPath());

        return [
            'contents' => $contents !== false ? $contents : '',
            'extension' => strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg'),
            'mime' => (string) $file->getMimeType(),
        ];
    }
}
