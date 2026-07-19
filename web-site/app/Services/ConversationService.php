<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ConversationService
{
    private const MESSAGE_SCAN_LIMIT = 500;

    public function __construct(private GenderFilterService $genderFilter) {}

    public function visiblePartnersQuery(User $viewer): Builder
    {
        return User::where('role', 'user')
            ->where('is_banned', false)
            ->where('id', '!=', $viewer->id)
            ->where(function ($q) use ($viewer) {
                $this->genderFilter->applyDiscoveryFilters($q, $viewer);
            });
    }

    public function isVisiblePartner(User $viewer, ?User $partner): bool
    {
        if (!$partner) {
            return false;
        }

        return $this->visiblePartnersQuery($viewer)
            ->where('id', $partner->id)
            ->exists();
    }

    public function unreadMessageCount(User $viewer): int
    {
        return Message::where('receiver_id', $viewer->id)
            ->where('is_read', false)
            ->whereNull('hidden_for_receiver_at')
            ->whereIn('sender_id', $this->visiblePartnersQuery($viewer)->select('id'))
            ->count();
    }

    public function buildConversations(User $viewer): Collection
    {
        $viewerId = $viewer->id;

        $sentPartnerIds = Message::query()
            ->where('sender_id', $viewerId)
            ->whereNull('hidden_for_sender_at')
            ->distinct()
            ->pluck('receiver_id');

        $receivedPartnerIds = Message::query()
            ->where('receiver_id', $viewerId)
            ->whereNull('hidden_for_receiver_at')
            ->distinct()
            ->pluck('sender_id');

        $messagePartnerIds = $sentPartnerIds->merge($receivedPartnerIds)->unique()->values();

        if ($messagePartnerIds->isEmpty()) {
            return collect();
        }

        $visiblePartnerIds = $this->visiblePartnersQuery($viewer)
            ->whereIn('id', $messagePartnerIds)
            ->pluck('id');

        if ($visiblePartnerIds->isEmpty()) {
            return collect();
        }

        $partnerIdList = $visiblePartnerIds->all();

        $sentLatest = Message::query()
            ->selectRaw('receiver_id as partner_id, MAX(id) as latest_id')
            ->where('sender_id', $viewerId)
            ->whereIn('receiver_id', $partnerIdList)
            ->whereNull('hidden_for_sender_at')
            ->groupBy('receiver_id')
            ->pluck('latest_id', 'partner_id');

        $receivedLatest = Message::query()
            ->selectRaw('sender_id as partner_id, MAX(id) as latest_id')
            ->where('receiver_id', $viewerId)
            ->whereIn('sender_id', $partnerIdList)
            ->whereNull('hidden_for_receiver_at')
            ->groupBy('sender_id')
            ->pluck('latest_id', 'partner_id');

        $latestByPartner = [];
        foreach ($sentLatest as $partnerId => $messageId) {
            $pid = (int) $partnerId;
            $latestByPartner[$pid] = max((int) $messageId, $latestByPartner[$pid] ?? 0);
        }
        foreach ($receivedLatest as $partnerId => $messageId) {
            $pid = (int) $partnerId;
            $latestByPartner[$pid] = max((int) $messageId, $latestByPartner[$pid] ?? 0);
        }

        if ($latestByPartner === []) {
            return collect();
        }

        $messages = Message::whereIn('id', array_values($latestByPartner))
            ->get()
            ->keyBy('id');

        $partnerIds = array_keys($latestByPartner);
        $partners = User::whereIn('id', $partnerIds)->get()->keyBy('id');

        $unreadCounts = Message::query()
            ->where('receiver_id', $viewerId)
            ->whereIn('sender_id', $partnerIds)
            ->where('is_read', false)
            ->whereNull('hidden_for_receiver_at')
            ->groupBy('sender_id')
            ->selectRaw('sender_id, COUNT(*) as aggregate')
            ->pluck('aggregate', 'sender_id');

        return collect($partnerIds)
            ->map(function (int $partnerId) use ($viewer, $latestByPartner, $messages, $partners, $unreadCounts) {
                $partner = $partners->get($partnerId);
                if (!$partner) {
                    return null;
                }

                $msg = $messages->get($latestByPartner[$partnerId]);
                if (!$msg) {
                    return null;
                }

                return [
                    'user' => $partner,
                    'last_message' => $msg->message_text,
                    'last_sender_name' => $msg->sender_id === $viewer->id ? __('app.messages.you') : $partner->username,
                    'last_message_at' => $msg->created_at,
                    'unread_count' => (int) ($unreadCounts[$partnerId] ?? 0),
                ];
            })
            ->filter()
            ->sortByDesc(fn ($row) => $row['last_message_at']?->getTimestamp() ?? 0)
            ->values();
    }

    public function purgeOrphanMessages(): int
    {
        return Message::where(function ($query) {
            $query->whereDoesntHave('sender')
                ->orWhereDoesntHave('receiver');
        })->delete();
    }
}

