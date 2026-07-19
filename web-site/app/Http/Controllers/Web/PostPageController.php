<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Post;
use App\Services\AiModerationService;
use App\Services\MediaUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostPageController extends Controller
{
    public function __construct(
        private MediaUploadService $mediaUpload,
        private AiModerationService $moderation,
    ) {}

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'image' => 'required|image|max:15360',
            'caption' => 'nullable|string|max:500',
        ], [
            'image.required' => 'Lütfen bir fotoğraf seçin.',
            'image.image' => 'Gönderi dosyası geçerli bir görsel olmalıdır.',
            'image.max' => 'Görsel en fazla 15 MB olabilir.',
        ]);

        if (! empty($validated['caption'])) {
            $this->moderation->validateOutgoingText($validated['caption'], 'post');
        }

        $imageUrl = null;

        try {
            $post = DB::transaction(function () use ($request, $validated, &$imageUrl) {
                $imageUrl = $this->mediaUpload->uploadPostImage($request->file('image'));

                return Post::create([
                    'user_id' => $request->user()->id,
                    'image_url' => $imageUrl,
                    'caption' => $validated['caption'] ?? null,
                ]);
            });
        } catch (\Throwable $e) {
            if ($imageUrl) {
                $this->mediaUpload->deleteByUrl($imageUrl);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gönderi yüklenemedi. Lütfen tekrar deneyin.',
                ], 500);
            }

            return back()->withErrors(['image' => 'Gönderi yüklenemedi. Lütfen tekrar deneyin.']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Gönderiniz paylaşıldı.',
                'data' => ['id' => $post->id],
            ]);
        }

        return back()->with('success', 'Gönderiniz paylaşıldı.');
    }

    /**
     * Tüm kullanıcılar kendi gönderi açıklamasını düzenleyebilir (paket gerekmez).
     */
    public function update(Request $request, Post $post): JsonResponse|RedirectResponse
    {
        $viewer = $request->user();
        if ($post->user_id !== $viewer->id && ! $viewer->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'caption' => 'nullable|string|max:500',
        ], [
            'caption.max' => 'Açıklama en fazla 500 karakter olabilir.',
        ]);

        $caption = trim((string) ($validated['caption'] ?? ''));
        $caption = $caption === '' ? null : $caption;

        if ($caption !== null) {
            $this->moderation->validateOutgoingText($caption, 'post');
        }

        $post->forceFill(['caption' => $caption])->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Açıklama güncellendi.',
                'data' => [
                    'id' => $post->id,
                    'caption' => $post->caption,
                ],
            ]);
        }

        return back()->with('success', 'Açıklama güncellendi.');
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        if ($post->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->mediaUpload->deleteByUrl($post->image_url);
        $post->delete();

        return back()->with('success', 'Gönderi silindi.');
    }

    public function toggleLike(Request $request, Post $post): JsonResponse
    {
        $userId = $request->user()->id;
        $like = Like::where('user_id', $userId)->where('post_id', $post->id)->first();

        if ($like) {
            $like->delete();
            $isLiked = false;
        } else {
            Like::create(['user_id' => $userId, 'post_id' => $post->id]);
            $isLiked = true;
        }

        $post->refresh();

        return response()->json([
            'success' => true,
            'is_liked' => $isLiked,
            'likes_count' => $post->likes_count,
        ]);
    }
}
