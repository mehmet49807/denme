<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use App\Services\ReferralService;
use App\Services\UserAttributionService;
use App\Support\SeoHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralPageController extends Controller
{
    public function __construct(private ReferralService $referrals) {}

    /** Auth: /davet — WhatsApp + kopyala + ödül özeti */
    public function index(): View
    {
        $user = auth()->user();
        $inviteUrl = $this->referrals->inviteUrl($user);
        $shareText = $user->gender === 'female'
            ? 'Gönül Köprüsü\'nde güvenli ve saygılı tanışma platformuna seni de bekliyorum. Ücretsiz kayıt ol:'
            : 'Gönül Köprüsü\'ne katıl, ciddi ilişki odaklı tanışma platformunu keşfet:';

        $referralCount = $this->referrals->referralCount($user);

        $recentReferrals = Referral::query()
            ->with('referred:id,username,first_name,created_at')
            ->where('referrer_id', $user->id)
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('web.referral', [
            'user' => $user,
            'inviteUrl' => $inviteUrl,
            'whatsappUrl' => $this->referrals->whatsappShareUrl($user, $shareText),
            'instagramUrl' => \App\Support\InstagramUrl::withUtm('referral', 'share', 'instagram'),
            'shareText' => $shareText,
            'referralCount' => $referralCount,
            'recentReferrals' => $recentReferrals,
            'rewardDays' => User::REFERRAL_REWARD_DAYS,
            'milestones' => $this->referrals->milestones($user),
            'nextMilestone' => $this->referrals->nextMilestone($user),
            'leaderboard' => $this->referrals->leaderboard(8),
        ]);
    }

    /** Paylaşım gerçekleşince onboarding davet adımını işaretle */
    public function markShared(Request $request): JsonResponse
    {
        session(['growth_invite_shared' => 1]);
        cookie()->queue('gk_invite_shared', '1', 60 * 24 * 30);

        return response()->json(['success' => true]);
    }

    /** Public: /davet/{code} — misafir davet landing */
    public function show(string $code): View|RedirectResponse
    {
        app(UserAttributionService::class)->captureFromRequest(request());

        $referrer = $this->referrals->findReferrerByCode($code);
        if (! $referrer) {
            abort(404);
        }

        if (auth()->check()) {
            return redirect()->route('referral');
        }

        session(['growth_ref' => strtoupper($this->referrals->ensureCode($referrer))]);

        $name = $referrer->first_name ?: $referrer->username;
        SeoHelper::setMultiple([
            'title' => $name.' seni Gönül Köprüsü\'ne davet etti',
            'description' => $name.' seni Gönül Köprüsü\'ne davet etti. Ücretsiz kayıt ol, güvenli ve ciddi ilişki odaklı tanışmaya başla.',
            'keywords' => 'davet, kayıt, tanışma, Gönül Köprüsü',
        ]);

        $registerUrl = route('register', [
            'ref' => $referrer->referral_code,
            'utm_source' => 'invite',
            'utm_medium' => 'landing',
            'utm_campaign' => 'referral',
        ]);

        $shareText = $name.' seni Gönül Köprüsü\'ne davet etti. Ücretsiz kayıt:';

        return view('web.invite-landing', [
            'referrer' => $referrer,
            'referrerName' => $name,
            'code' => $referrer->referral_code,
            'registerUrl' => $registerUrl,
            'whatsappUrl' => 'https://wa.me/?text='.rawurlencode($shareText.' '.$registerUrl),
            'rewardDays' => User::REFERRAL_REWARD_DAYS,
        ]);
    }
}
