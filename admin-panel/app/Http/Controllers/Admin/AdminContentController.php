<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Story;
use App\Services\MediaUploadService;
use App\Services\StoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminContentController extends Controller
{
    public function __construct(
        private MediaUploadService $mediaUpload,
        private StoryService $stories,
    ) {}

    public function index(Request $request): View
    {
        Story::ensureAudienceColumn();

        $tab = $request->get('tab') === 'stories' ? 'stories' : 'posts';
        $search = trim((string) $request->get('search', ''));

        $stats = [
            'posts' => Post::count(),
            'stories_total' => Story::count(),
            'stories_active' => Story::where('expires_at', '>', now())->count(),
        ];

        if ($tab === 'stories') {
            $this->stories->purgeExpiredIfNeeded();

            $query = Story::with('user')->orderByDesc('created_at');

            if ($search !== '') {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            }

            $items = $query->paginate(24)->withQueryString();
        } else {
            $query = Post::with('user')->orderByDesc('created_at');

            if ($search !== '') {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            }

            $items = $query->paginate(24)->withQueryString();
        }

        return view('admin.content', [
            'tab' => $tab,
            'search' => $search,
            'stats' => $stats,
            'items' => $items,
        ]);
    }

    public function storeStory(Request $request): RedirectResponse
    {
        Story::ensureAudienceColumn();

        $validated = $request->validate([
            'media' => 'required|file|mimes:jpeg,jpg,png,gif,webp,mp4,mov,webm|max:25600',
            'audience' => 'required|in:all,male,female',
        ], [
            'media.required' => 'Lütfen bir fotoğraf veya video seçin.',
            'media.mimes' => 'Hikâye dosyası JPG, PNG, GIF, WEBP, MP4, MOV veya WEBM olmalıdır.',
            'media.max' => 'Hikâye dosyası en fazla 25 MB olabilir.',
            'audience.required' => 'Hedef kitle seçin.',
            'audience.in' => 'Geçersiz hedef kitle.',
        ]);

        $file = $request->file('media');
        $mediaUrl = null;

        try {
            DB::transaction(function () use ($request, $file, $validated, &$mediaUrl) {
                $mediaUrl = $this->mediaUpload->uploadStoryMedia($file);

                $this->stories->createForUser(
                    $request->user(),
                    $mediaUrl,
                    $this->detectMediaType($file),
                    $validated['audience'],
                );
            });
        } catch (\Throwable $e) {
            if ($mediaUrl) {
                $this->mediaUpload->deleteByUrl($mediaUrl);
            }

            Log::error('Admin story upload failed', [
                'admin_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.content', ['tab' => 'stories'])
                ->withErrors(['media' => 'Hikâye yüklenemedi. Lütfen tekrar deneyin.']);
        }

        $label = Story::audienceLabel($validated['audience']);

        return redirect()
            ->route('admin.content', ['tab' => 'stories'])
            ->with('success', "Hikâye paylaşıldı — hedef: {$label}.");
    }

    public function destroyPost(Post $post): RedirectResponse
    {
        $this->mediaUpload->deleteByUrl($post->image_url);
        $post->delete();

        return redirect()
            ->route('admin.content', ['tab' => 'posts', 'search' => request('search'), 'page' => request('page')])
            ->with('success', 'Gönderi ve görseli silindi.');
    }

    public function destroyStory(Story $story): RedirectResponse
    {
        $this->mediaUpload->deleteByUrl($story->media_url);
        $story->delete();

        return redirect()
            ->route('admin.content', ['tab' => 'stories', 'search' => request('search'), 'page' => request('page')])
            ->with('success', 'Hikaye ve medya dosyası silindi.');
    }

    private function detectMediaType(UploadedFile $file): string
    {
        return str_starts_with((string) $file->getMimeType(), 'video/') ? 'video' : 'image';
    }
}
