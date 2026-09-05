@if($conversations->isNotEmpty())
@php $countryMeta = app(\App\Services\CountryMetaService::class); @endphp
<ul class="conversation-list" data-inbox-swipe-list>
    @foreach($conversations as $conversation)
    @php
        $user = $conversation['user'];
        $isActive = isset($activeUsername) && $activeUsername === $user->username;
        $country = trim((string) ($user->country ?: 'Türkiye'));
        $city = trim((string) ($user->city ?: ''));
        $iso = $countryMeta->isoForCountry($country !== '' ? $country : 'Türkiye');
        $flagUrl = $countryMeta->flagUrl($iso !== '' ? $iso : 'tr');
        $locationLabel = collect([$country, $city])->filter()->implode(' · ');
    @endphp
    <li class="conversation-row" data-username="{{ $user->username }}" data-swipe-row>
        <div class="conversation-swipe-rail" aria-hidden="true">
            <form method="POST" action="{{ route('messages.block', $user->username) }}" class="conversation-swipe-block-form" data-inbox-block data-confirm="{{ __('app.messages.block_confirm', ['name' => $user->username]) }}">
                @csrf
                <button type="submit" class="conversation-swipe-action conversation-swipe-action--block" tabindex="-1">
                    <span>{{ __('app.messages.block') }}</span>
                </button>
            </form>
            <button type="button" class="conversation-swipe-action conversation-swipe-action--delete" data-inbox-clear data-clear-url="{{ route('messages.clear', $user->username) }}" data-confirm="{{ __('app.messages.clear_confirm') }}" data-failed="{{ __('app.messages.clear_failed') }}" tabindex="-1">
                <span>{{ __('app.messages.delete') }}</span>
            </button>
        </div>
        <div class="conversation-swipe-front">
            <a href="{{ route('messages.show', $user->username) }}" class="conversation-item {{ $conversation['unread_count'] > 0 ? 'conversation-item--unread' : '' }} {{ $isActive ? 'conversation-item--active' : '' }}">
                @php $_convPkg = method_exists($user, 'activePackageType') ? $user->activePackageType() : null; @endphp
                <div class="conversation-avatar{{ in_array($_convPkg, ['pro','gold','platinum']) ? ' is-premium-'.$_convPkg : '' }}">
                    @if($user->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->username }}" width="56" height="56" loading="lazy" decoding="async">
                    @else
                        {{ strtoupper(substr($user->username, 0, 1)) }}
                    @endif
                    @include('partials.online-status-badge', ['user' => $user, 'size' => 'sm'])
                </div>
                <div class="conversation-body">
                    <div class="conversation-top">
                        <span class="conversation-name-wrap">
                            <span class="conversation-name">{{ $user->username }}</span>
                            @include('partials.profile-verified-tick', ['user' => $user, 'size' => 'sm'])
                        </span>
                        @if($conversation['last_message_at'])
                            <time class="conversation-time" datetime="{{ $conversation['last_message_at']->toIso8601String() }}">{{ $conversation['last_message_at']->format('d.m.Y H:i') }}</time>
                        @endif
                    </div>
                    <div class="conversation-meta-line">
                        <img class="conversation-flag" src="{{ $flagUrl }}" alt="" width="16" height="12" loading="lazy" decoding="async">
                        <span class="conversation-location">{{ $locationLabel }}</span>
                    </div>
                    <p class="conversation-preview">
                        @if($conversation['last_sender_name'])
                            <span class="conversation-sender">{{ $conversation['last_sender_name'] }}:</span>
                        @endif
                        {{ Str::limit($conversation['last_message'], 70) }}
                    </p>
                </div>
                @if($conversation['unread_count'] > 0)
                    <span class="conversation-badge">{{ $conversation['unread_count'] }}</span>
                @endif
            </a>
        </div>
    </li>
    @endforeach
</ul>
@else
@include('partials.empty-state', [
    'class' => 'messages-empty',
    'icon' => 'messages',
    'title' => 'Henüz sohbet yok',
    'text' => 'Keşfette birini beğen veya eşleşmelerinden mesaj gönder. İlk adımı sen at.',
    'ctaUrl' => route('search'),
    'ctaLabel' => 'Üye keşfet',
])
@endif
