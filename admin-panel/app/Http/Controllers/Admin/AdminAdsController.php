<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class AdminAdsController extends Controller
{
    private const ALLOWED_EXT = ['mp4', 'png', 'jpg', 'jpeg', 'webp'];

    public function index(): View
    {
        $frontend = rtrim((string) config('app.frontend_url', 'https://gonulkoprusu.com'), '/');
        $base = $frontend.'/images/ads';
        $catalog = $this->loadCatalog($base);

        return view('admin.ads', [
            'frontendUrl' => $frontend,
            'adsBaseUrl' => $base,
            'videos' => $catalog['videos'],
            'photos' => $catalog['photos'],
            'researchNotes' => $catalog['research_notes'],
            'videoCount' => count($catalog['videos']),
            'photoCount' => count($catalog['photos']),
        ]);
    }

    /**
     * Same-origin download proxy — browser ignores download="" on cross-origin CDN URLs.
     */
    public function download(Request $request): BinaryFileResponse|StreamedResponse
    {
        $file = basename((string) $request->query('file', ''));
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if ($file === '' || $file === '.' || $file === '..' || ! in_array($ext, self::ALLOWED_EXT, true)) {
            abort(404, 'Dosya bulunamadı.');
        }

        if (! preg_match('/^[A-Za-z0-9._-]+$/', $file)) {
            abort(404, 'Dosya bulunamadı.');
        }

        $mime = match ($ext) {
            'mp4' => 'video/mp4',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        $localDir = $this->localAdsDir();
        $localPath = $localDir ? $localDir.DIRECTORY_SEPARATOR.$file : null;
        if (is_string($localPath) && is_file($localPath)) {
            return response()->download($localPath, $file, [
                'Content-Type' => $mime,
                'Cache-Control' => 'private, max-age=3600',
            ]);
        }

        $frontend = rtrim((string) config('app.frontend_url', 'https://gonulkoprusu.com'), '/');
        $remote = $frontend.'/images/ads/'.$file;

        $tmp = tempnam(sys_get_temp_dir(), 'gkads_');
        if ($tmp === false) {
            abort(500, 'Geçici dosya oluşturulamadı.');
        }

        $ctx = stream_context_create([
            'http' => [
                'timeout' => 120,
                'header' => "User-Agent: GonulKoprusuAdmin/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $ok = @copy($remote, $tmp, $ctx);
        if (! $ok || ! is_file($tmp) || filesize($tmp) < 32) {
            @unlink($tmp);
            abort(404, 'Dosya bulunamadı.');
        }

        return response()->download($tmp, $file, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=3600',
        ])->deleteFileAfterSend(true);
    }

    private function downloadUrl(string $file): string
    {
        return route('admin.ads.download', ['file' => $file]);
    }

    /**
     * @return array{
     *   videos: list<array<string, mixed>>,
     *   photos: list<array<string, mixed>>,
     *   research_notes: list<string>
     * }
     */
    private function loadCatalog(string $base): array
    {
        $manifest = $this->readManifest();
        $videos = [];
        $photos = [];

        foreach (($manifest['videos'] ?? []) as $item) {
            $file = (string) ($item['file'] ?? '');
            if ($file === '') {
                continue;
            }
            $poster = (string) ($item['poster'] ?? '');
            $videos[] = [
                'id' => pathinfo($file, PATHINFO_FILENAME),
                'title' => (string) ($item['title'] ?? $file),
                'subtitle' => (string) ($item['subtitle'] ?? ''),
                'format' => (string) ($item['format'] ?? $this->guessFormat($file)),
                'channel' => (string) ($item['channel'] ?? ''),
                'kind' => (string) ($item['kind'] ?? (str_starts_with($file, 'rx-') ? 'realistic' : 'classic')),
                'video_url' => $base.'/'.$file,
                'poster_url' => $poster !== '' ? $base.'/'.$poster : '',
                'download_url' => $this->downloadUrl($file),
                'file' => $file,
            ];
        }

        foreach (($manifest['photos'] ?? []) as $item) {
            $file = (string) ($item['file'] ?? '');
            if ($file === '') {
                continue;
            }
            $photos[] = [
                'id' => pathinfo($file, PATHINFO_FILENAME),
                'title' => (string) ($item['title'] ?? $file),
                'kind' => (string) ($item['kind'] ?? 'still'),
                'url' => $base.'/'.$file,
                'download_url' => $this->downloadUrl($file),
                'file' => $file,
            ];
        }

        // Local filesystem fallback / enrichment (monorepo & deploy with public tree)
        $localDir = $this->localAdsDir();
        if ($localDir !== null) {
            foreach (File::files($localDir) as $path) {
                $name = $path->getFilename();
                $ext = strtolower($path->getExtension());
                if (in_array($name, ['README.txt', 'manifest.json', 'index.html'], true)) {
                    continue;
                }
                if ($ext === 'mp4') {
                    if (collect($videos)->contains(fn ($v) => $v['file'] === $name)) {
                        continue;
                    }
                    $poster = pathinfo($name, PATHINFO_FILENAME).'.png';
                    $videos[] = [
                        'id' => pathinfo($name, PATHINFO_FILENAME),
                        'title' => str_replace('-', ' ', pathinfo($name, PATHINFO_FILENAME)),
                        'subtitle' => '',
                        'format' => $this->guessFormat($name),
                        'channel' => '',
                        'kind' => str_starts_with($name, 'rx-') ? 'realistic' : 'classic',
                        'video_url' => $base.'/'.$name,
                        'poster_url' => File::exists($localDir.'/'.$poster) ? $base.'/'.$poster : '',
                        'download_url' => $this->downloadUrl($name),
                        'file' => $name,
                    ];
                } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                    if (collect($photos)->contains(fn ($p) => $p['file'] === $name)) {
                        continue;
                    }
                    $photos[] = [
                        'id' => pathinfo($name, PATHINFO_FILENAME),
                        'title' => str_replace('-', ' ', pathinfo($name, PATHINFO_FILENAME)),
                        'kind' => str_contains($name, 'still') ? 'still' : 'poster',
                        'url' => $base.'/'.$name,
                        'download_url' => $this->downloadUrl($name),
                        'file' => $name,
                    ];
                }
            }
        }

        usort($videos, static function (array $a, array $b): int {
            $rank = static fn (array $x): int => ($x['kind'] === 'realistic' ? 0 : 1);
            $cmp = $rank($a) <=> $rank($b);
            if ($cmp !== 0) {
                return $cmp;
            }

            return strcmp((string) $a['file'], (string) $b['file']);
        });

        usort($photos, static fn (array $a, array $b): int => strcmp((string) $a['file'], (string) $b['file']));

        $notes = $manifest['research_notes'] ?? [
            'Meta Reels/Stories: 9:16; hook ilk 2 sn',
            'Dating: kimlik odaklı mesaj (ciddi ilişki)',
            'Gerçek görüntü + altyazı + 15–30 sn',
        ];

        return [
            'videos' => array_values($videos),
            'photos' => array_values($photos),
            'research_notes' => array_values($notes),
        ];
    }

    /** @return array<string, mixed> */
    private function readManifest(): array
    {
        $candidates = [
            resource_path('data/ads-manifest.json'),
            base_path('../web-site/public/images/ads/manifest.json'),
            base_path('public/images/ads/manifest.json'),
        ];

        foreach ($candidates as $path) {
            if (! is_string($path) || ! is_file($path)) {
                continue;
            }
            try {
                /** @var array<string, mixed> $data */
                $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

                return $data;
            } catch (\Throwable) {
                continue;
            }
        }

        return [];
    }

    private function localAdsDir(): ?string
    {
        $candidates = [
            base_path('../web-site/public/images/ads'),
            base_path('public/images/ads'),
            resource_path('../public/images/ads'),
        ];
        foreach ($candidates as $dir) {
            if (is_dir($dir)) {
                return $dir;
            }
        }

        return null;
    }

    private function guessFormat(string $file): string
    {
        if (str_contains($file, 'story-') || (str_starts_with($file, 'rx-') && ! str_contains($file, '-wide') && ! str_starts_with($file, 'rx-05'))) {
            return '9:16';
        }

        return '16:9';
    }
}
