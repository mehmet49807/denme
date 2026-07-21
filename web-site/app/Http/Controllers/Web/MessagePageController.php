<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Message;
use App\Models\User;
use App\Services\AiModerationService;
use App\Services\ChatTypingService;
use App\Services\ConversationService;
use App\Services\MessageConversationService;
use App\Services\MessageService;
use App\Services\NotificationService;
use App\Support\ChatMessageHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MessagePageController extends Controller
{
    public function __construct(
        private ConversationService $conversations,
        private MessageConversationService $messageConversations,
        private MessageService $messageService,
        private ChatTypingService $typing,
        private NotificationService $notifications,
        private AiModerationService $moderation,
    ) {}

    public function index(Request $request): View
    {
        $viewer = $request->user();

        return view('web.messages.index', [
            'viewer' => $viewer,
            'conversations' => $this->conversations->buildConversations($viewer, true),
        ]);
    }

    public function show(Request $request, string $username): View|RedirectResponse
    {
        $viewer = $request->user();
        $partner = $this->resolveChatPartner($viewer, $username);

        if (! $partner) {
            abort(404);
        }

        $messages = $this->threadMessagesQuery($viewer, $partner)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->reverse()
            ->values();

        Message::where('sender_id', $partner->id)
            ->where('receiver_id', $viewer->id)
            ->where('is_read', false)
            ->when($partner->wantsReadReceipts(), function ($q) {
                $q->update(['is_read' => true, 'read_at' => now()]);
            }, function ($q) {
                $q->update(['is_read' => true]);
            });

        $this->notifications->markMessageNotificationsRead($viewer, $partner->id);

        return view('web.messages.show', [
            'viewer' => $viewer,
            'partner' => $partner,
            'messages' => $messages,
            'conversations' => $this->conversations->buildConversations($viewer, true),
        ]);
    }

    public function store(Request $request, string $username): RedirectResponse|JsonResponse
    {
        try {
            $request->validate([
                'message_text' => 'nullable|string|max:2000',
                'attachment' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,mp3,wav,ogg,m4a,webm|max:8192',
            ], [
                'message_text.max' => __('app.messages.failed'),
            ]);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => collect($e->errors())->flatten()->first(),
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        }

        $viewer = $request->user();
        $partner = $this->resolveChatPartner($viewer, $username);

        if (! $partner) {
            abort(404);
        }

        if (! $this->messageConversations->canSendTo($viewer, $partner)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => __('app.messages.failed'),
                ], 403);
            }

            return back()->withErrors(['message_text' => __('app.messages.failed')]);
        }

        $text = trim((string) $request->input('message_text', ''));
        $attachmentUrl = null;
        $attachmentType = null;

        if ($request->hasFile('attachment')) {
            try {
                $file = $request->file('attachment');
                $attachmentUrl = app(\App\Services\MediaUploadService::class)->uploadProfilePhoto($file);
                $mime = (string) $file->getMimeType();
                $attachmentType = str_starts_with($mime, 'audio/') ? 'audio' : 'image';
            } catch (\Throwable) {
                return back()->withErrors(['attachment' => 'Dosya yüklenemedi.']);
            }
        }

        if ($text === '' && ! $attachmentUrl) {
            return back()->withErrors(['message_text' => __('app.messages.label')]);
        }

        if ($text !== '') {
            try {
                $this->moderation->validateOutgoingText($text, 'message');
            } catch (ValidationException $e) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'ok' => false,
                        'message' => collect($e->errors())->flatten()->first(),
                        'errors' => $e->errors(),
                    ], 422);
                }

                throw $e;
            }
        }

        $message = Message::create([
            'sender_id' => $viewer->id,
            'receiver_id' => $partner->id,
            'message_text' => $text !== '' ? $text : ($attachmentType === 'audio' ? '🎤 Sesli mesaj' : '📷 Görsel'),
            'attachment_url' => $attachmentUrl,
            'attachment_type' => $attachmentType,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $this->serializeMessage($message, $viewer),
            ]);
        }

        return redirect()
            ->route('messages.show', $partner->username)
            ->with('success', __('app.messages.send'));
    }

    public function inboxPoll(Request $request): JsonResponse
    {
        $viewer = $request->user();
        $conversations = $this->conversations->buildConversations($viewer, true);
        $activeUsername = $request->query('active');
        $counts = \App\Support\SidebarBadgeCounts::forUser($viewer);

        return response()->json([
            'success' => true,
            'data' => [
                'html' => view('web.messages.partials.inbox-body', [
                    'conversations' => $conversations,
                    'activeUsername' => $activeUsername,
                ])->render(),
                'unread_messages' => $counts['messages'],
                'unread_notifications' => $counts['notifications'],
            ],
        ]);
    }

    public function poll(Request $request, string $username): JsonResponse
    {
        $viewer = $request->user();
        $partner = $this->resolveChatPartner($viewer, $username);

        if (! $partner) {
            return response()->json(['ok' => false], 404);
        }

        $after = max(0, (int) $request->query('after', 0));

        $messages = $this->threadMessagesQuery($viewer, $partner)
            ->when($after > 0, fn ($q) => $q->where('id', '>', $after))
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->map(fn (Message $message) => $this->serializeMessage($message, $viewer))
            ->values();

        if ($messages->isNotEmpty()) {
            Message::where('sender_id', $partner->id)
                ->where('receiver_id', $viewer->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        return response()->json([
            'ok' => true,
            'messages' => $messages,
        ]);
    }

    public function pingTyping(Request $request, string $username): JsonResponse
    {
        $viewer = $request->user();
        $partner = $this->resolveChatPartner($viewer, $username);

        if (! $partner) {
            return response()->json(['ok' => false], 404);
        }

        $this->typing->ping($viewer->id, $partner->id);

        return response()->json(['ok' => true]);
    }

    public function typingStatus(Request $request, string $username): JsonResponse
    {
        $viewer = $request->user();
        $partner = $this->resolveChatPartner($viewer, $username);

        if (! $partner) {
            return response()->json(['typing' => false], 404);
        }

        return response()->json([
            'typing' => $this->typing->isTyping($partner->id, $viewer->id),
        ]);
    }

    public function destroy(Request $request, string $username, Message $message): JsonResponse
    {
        $viewer = $request->user();
        $partner = $this->resolveChatPartner($viewer, $username);

        if (! $partner) {
            return response()->json(['ok' => false], 404);
        }

        $belongsToThread = (
            ($message->sender_id === $viewer->id && $message->receiver_id === $partner->id)
            || ($message->sender_id === $partner->id && $message->receiver_id === $viewer->id)
        );

        if (! $belongsToThread) {
            return response()->json(['ok' => false], 403);
        }

        $this->messageService->hideForUser($message, $viewer);

        return response()->json(['ok' => true]);
    }

    public function clearConversation(Request $request, string $username): JsonResponse
    {
        $viewer = $request->user();
        $partner = $this->resolveChatPartner($viewer, $username);

        if (! $partner) {
            return response()->json(['ok' => false], 404);
        }

        $this->threadMessagesQuery($viewer, $partner)
            ->orderBy('id')
            ->chunkById(100, function ($messages) use ($viewer) {
                foreach ($messages as $message) {
                    $this->messageService->hideForUser($message, $viewer);
                }
            });

        return response()->json(['ok' => true]);
    }

    public function block(Request $request, string $username): RedirectResponse
    {
        $viewer = $request->user();
        $partner = User::where('username', $username)->where('role', 'user')->firstOrFail();

        if ($partner->id === $viewer->id) {
            abort(422);
        }

        Block::firstOrCreate([
            'blocker_id' => $viewer->id,
            'blocked_id' => $partner->id,
        ]);

        return redirect()
            ->route('messages.index')
            ->with('success', __('app.messages.blocked', ['name' => $partner->username]));
    }

    private function resolveChatPartner(User $viewer, string $username): ?User
    {
        $partner = User::where('username', $username)->where('role', 'user')->first();

        if (! $partner || $partner->id === $viewer->id) {
            return null;
        }

        if (! $this->messageConversations->canOpenChat($viewer, $partner)) {
            return null;
        }

        return $partner;
    }

    private function threadMessagesQuery(User $viewer, User $partner)
    {
        $query = Message::query()->where(function ($q) use ($viewer, $partner) {
            $q->where('sender_id', $viewer->id)->where('receiver_id', $partner->id);
        })->orWhere(function ($q) use ($viewer, $partner) {
            $q->where('sender_id', $partner->id)->where('receiver_id', $viewer->id);
        });

        return $this->messageService->visibleToUser($query, $viewer->id);
    }

    private function serializeMessage(Message $message, User $viewer): array
    {
        $createdAt = $message->created_at;

        return [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'message_text' => $message->message_text,
            'created_at' => $createdAt?->toIso8601String(),
            'created_at_display' => $createdAt?->format('d.m.Y H:i'),
            'is_emoji_only' => ChatMessageHelper::isEmojiOnly((string) $message->message_text),
        ];
    }
}
