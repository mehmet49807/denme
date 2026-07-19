<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Collection;

class MessageConversationService
{
    public function __construct(private GenderFilterService $genderFilter) {}

    public function isValidPartner(User $viewer, User $partner): bool
    {
        return $partner->role === 'user'
            && $partner->id !== $viewer->id
            && $partner->gender !== $viewer->gender;
    }

    public function hasHistory(User $viewer, User $partner): bool
    {
        return $this->messagesBetween($viewer, $partner)->isNotEmpty();
    }

    public function canOpenChat(User $viewer, User $partner): bool
    {
        if (!$this->isValidPartner($viewer, $partner)) {
            return false;
        }

        if ($this->hasHistory($viewer, $partner)) {
            return true;
        }

        return User::where('id', $partner->id)
            ->where(function ($q) use ($viewer) {
                $this->genderFilter->applyDiscoveryFilters($q, $viewer);
            })
            ->exists();
    }

    public function canSendTo(User $viewer, User $partner): bool
    {
        if (!$viewer->canSendMessages() || !$this->canOpenChat($viewer, $partner)) {
            return false;
        }

        return User::where('id', $partner->id)
            ->where(function ($q) use ($viewer) {
                $this->genderFilter->applyDiscoveryFilters($q, $viewer);
            })
            ->exists();
    }

    public function messagesBetween(User $viewer, User $partner): Collection
    {
        return Message::query()
            ->where(function ($q) use ($viewer, $partner) {
                $q->where('sender_id', $viewer->id)->where('receiver_id', $partner->id);
            })
            ->orWhere(function ($q) use ($viewer, $partner) {
                $q->where('sender_id', $partner->id)->where('receiver_id', $viewer->id);
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    public function buildConversations(User $viewer): Collection
    {
        $messages = Message::query()
            ->where(function ($q) use ($viewer) {
                $q->where('sender_id', $viewer->id)
                    ->orWhere('receiver_id', $viewer->id);
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $latestByPartner = [];
        $countsByPartner = [];

        foreach ($messages as $msg) {
            $partnerId = $msg->sender_id === $viewer->id ? $msg->receiver_id : $msg->sender_id;
            $countsByPartner[$partnerId] = ($countsByPartner[$partnerId] ?? 0) + 1;

            if (!isset($latestByPartner[$partnerId])) {
                $latestByPartner[$partnerId] = $msg;
            }
        }

        $conversations = collect();

        foreach ($latestByPartner as $partnerId => $msg) {
            $partner = User::find($partnerId);

            if (!$partner || !$this->isValidPartner($viewer, $partner)) {
                continue;
            }

            $conversations->push([
                'user' => $partner,
                'last_message' => $msg->message_text,
                'last_message_at' => $msg->created_at,
                'message_count' => $countsByPartner[$partnerId] ?? 0,
                'unread_count' => Message::where('sender_id', $partnerId)
                    ->where('receiver_id', $viewer->id)
                    ->where('is_read', false)
                    ->count(),
            ]);
        }

        return $conversations->sortByDesc(fn (array $item) => $item['last_message_at']?->timestamp ?? 0)->values();
    }

    public function markAsRead(User $viewer, User $partner): void
    {
        Message::where('sender_id', $partner->id)
            ->where('receiver_id', $viewer->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }
}
