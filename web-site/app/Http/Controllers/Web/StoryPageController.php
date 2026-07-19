<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Services\MediaUploadService;
use App\Services\StoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class StoryPageController extends Controller
{
    public function __construct(
        private MediaUploadService $mediaUpload,
        private StoryService $stories,
    ) {}

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        if (!$user->canPostStories()) {
            $message = __('app.feed.story_premium_required');

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }

            return back()->withErrors(['story' => $message]);
        }

        $request->validate([
            'media' => 'required|file|mimes:jpeg,jpg,png,gif,webp,mp4,mov,webm|max:25600',
        ], [
            'media.required' => 'Lütfen bir fotoğraf veya video seçin.',
            'media.mimes' => 'Hikaye dosyası JPG, PNG, GIF, WEBP, MP4, MOV veya WEBM formatında olmalıdır.',
            'media.max' => 'Hikaye dosyası en fazla 25 MB olabilir.',
        ]);

        $file = $request->file('media');

        if ($this->detectMediaType($file) === 'video') {
            $duration = $this->getVideoDurationSeconds($file);
            if ($duration !== null && $duration > 15.5) {
                $message = 'Hikaye videoları en fazla 15 saniye olabilir. Lütfen daha kısa bir video seçin.';

                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $message, 'errors' => ['media' => [$message]]], 422);
                }

                return back()->withErrors(['story' => $message]);
            }
        }
        $mediaUrl = null;

        try {
            $story = DB::transaction(function () use ($user, $file, &$mediaUrl) {
                $mediaUrl = $this->mediaUpload->uploadStoryMedia($file);

                return $this->stories->createForUser(
                    $user,
                    $mediaUrl,
                    $this->detectMediaType($file),
                );
            });
        } catch (\Throwable $e) {
            if ($mediaUrl) {
                $this->mediaUpload->deleteByUrl($mediaUrl);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hikaye yüklenemedi. Lütfen tekrar deneyin.',
                ], 500);
            }

            return back()->withErrors(['story' => 'Hikaye yüklenemedi. Lütfen tekrar deneyin.']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Hikayeniz paylaşıldı.',
                'data' => ['id' => $story->id],
            ]);
        }

        return back()->with('success', 'Hikayeniz paylaşıldı.');
    }

    public function destroy(Request $request, Story $story): JsonResponse|RedirectResponse
    {
        if ($story->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->mediaUpload->deleteByUrl($story->media_url);
        $story->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Hikaye silindi.');
    }

    private function detectMediaType(UploadedFile $file): string
    {
        return str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image';
    }

    private function getVideoDurationSeconds(UploadedFile $file): ?float
    {
        $path = $file->getRealPath();
        if (!$path || !is_readable($path)) {
            return null;
        }

        $escaped = escapeshellarg($path);
        foreach (['ffprobe', '/usr/bin/ffprobe', '/usr/local/bin/ffprobe'] as $bin) {
            $cmd = $bin . ' -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 ' . $escaped . ' 2>/dev/null';
            $output = trim((string) @\shell_exec($cmd));
            if ($output !== '' && is_numeric($output)) {
                return (float) $output;
            }
        }

        return null;
    }
}
