<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class MediaUploadService
{
    public const FOLDER_PROFILES = 'profiles';
    public const FOLDER_POSTS = 'posts';
    public const FOLDER_STORIES = 'stories';

    private string $disk;
    private string $fallbackDisk;
    private string $baseUrl;

    public function __construct(private MediaOptimizer $optimizer)
    {
        $configured = (string) config('filesystems.media.disk', 'media_local');
        $localRoot = (string) config('filesystems.disks.media_local.root');

        if ($this->localUploadsReady($localRoot)) {
            $this->disk = 'media_local';
            $this->fallbackDisk = $configured !== 'media_local'
                ? $configured
                : (string) config('filesystems.media.fallback_disk', 'ftp_media');
        } else {
            $this->disk = $configured;
            $this->fallbackDisk = 'media_local';
        }

        $this->baseUrl = rtrim((string) config('filesystems.media.url', 'https://gonulkoprusu.com/uploads'), '/');
    }

    /**
     * Dosyayı yükler ve herkese açık URL döner.
     */
    public function upload(UploadedFile $file, string $folder, bool $optimizeImage = false): string
    {
        if ($optimizeImage && str_starts_with((string) $file->getMimeType(), 'image/')) {
            $payload = $this->optimizer->optimizeImage($file);
            $filename = Str::uuid().'.'.$payload['extension'];
            $path = "{$folder}/{$filename}";

            return $this->storePayload($path, $payload['contents']);
        }

        $filename = $this->generateFilename($file);
        $path = "{$folder}/{$filename}";
        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            throw new RuntimeException('Dosya okunamadı.');
        }

        return $this->storePayload($path, $contents);
    }

    public function uploadProfilePhoto(UploadedFile $file): string
    {
        return $this->upload($file, self::FOLDER_PROFILES, true);
    }

    public function uploadPostImage(UploadedFile $file): string
    {
        return $this->upload($file, self::FOLDER_POSTS, true);
    }

    public function uploadStoryMedia(UploadedFile $file): string
    {
        $isImage = str_starts_with((string) $file->getMimeType(), 'image/');

        return $this->upload($file, self::FOLDER_STORIES, $isImage);
    }

    public function deleteByUrl(?string $url): void
    {
        if (!$url) {
            return;
        }

        $path = $this->urlToPath($url);
        if (!$path) {
            return;
        }

        foreach (array_unique([$this->disk, $this->fallbackDisk]) as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->delete($path);
                }
            } catch (\Throwable) {
                // Silme hatası yüklemeyi engellemesin.
            }
        }
    }

    private function storePayload(string $path, string $contents): string
    {
        $folder = Str::before($path, '/');
        $this->ensureFolderExists($folder, $this->disk);

        if ($this->store($this->disk, $path, $contents)) {
            return "{$this->baseUrl}/{$path}";
        }

        if ($this->disk !== $this->fallbackDisk) {
            $this->ensureFolderExists($folder, $this->fallbackDisk);

            if ($this->store($this->fallbackDisk, $path, $contents)) {
                return "{$this->baseUrl}/{$path}";
            }
        }

        Log::error('Media upload failed on all disks.', [
            'path' => $path,
            'primary' => $this->disk,
            'fallback' => $this->fallbackDisk,
        ]);

        throw new RuntimeException('Dosya yüklenemedi.');
    }

    private function store(string $disk, string $path, string $contents): bool
    {
        try {
            return Storage::disk($disk)->put($path, $contents) === true;
        } catch (\Throwable $e) {
            Log::warning('Media disk write failed.', ['disk' => $disk, 'path' => $path, 'error' => $e->getMessage()]);

            return false;
        }
    }

    private function urlToPath(string $url): ?string
    {
        $prefix = $this->baseUrl.'/';
        if (!str_starts_with($url, $prefix)) {
            return null;
        }

        return substr($url, strlen($prefix));
    }

    private function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg';

        return Str::uuid().'.'.strtolower($extension);
    }

    private function ensureFolderExists(string $folder, string $disk): void
    {
        $storage = Storage::disk($disk);

        if (!$storage->exists($folder)) {
            $storage->makeDirectory($folder);
        }
    }

    private function localUploadsReady(string $root): bool
    {
        if (!is_dir($root) && !@mkdir($root, 0755, true)) {
            return false;
        }

        return is_writable($root);
    }
}
