<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Report;
use App\Models\PremiumSubscription;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        $search = trim((string) $request->get('search', ''));
        if (mb_strlen($search) > 100) {
            $search = mb_substr($search, 0, 100);
        }
        if ($search !== '') {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function ($q) use ($like) {
                $q->where('username', 'like', $like)
                  ->orWhere('email', 'like', $like)
                  ->orWhere('first_name', 'like', $like)
                  ->orWhere('last_name', 'like', $like);
            });
        }

        $gender = (string) $request->get('gender', '');
        if (in_array($gender, ['male', 'female'], true)) {
            $query->where('gender', $gender);
        }

        $role = (string) $request->get('role', '');
        if (in_array($role, ['user', 'moderator', 'support', 'admin'], true)) {
            $query->where('role', $role);
        }

        if ($request->get('banned') === '1') {
            $query->where('is_banned', true);
        } elseif ($request->get('banned') === '0') {
            $query->where('is_banned', false);
        }

        if ($request->get('verified') === '1') {
            $query->where('is_verified', true);
        } elseif ($request->get('verified') === '0') {
            $query->where('is_verified', false);
        }

        if ($request->get('email_verified') === '1') {
            $query->whereNotNull('email_verified_at');
        } elseif ($request->get('email_verified') === '0') {
            $query->whereNull('email_verified_at');
        }

        $users = $query->latest()->paginate(30)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['posts', 'premiumSubscriptions']);
        $reportsAgainst = Report::with('reporter')
            ->where('reported_id', $user->id)
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.users.show', compact('user', 'reportsAgainst'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:user,moderator,support,admin',
        ]);

        $user->forceFill(['role' => $validated['role']])->save();

        return back()->with('success', 'Kullanıcı rolü güncellendi.');
    }

    public function ban(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $user->forceFill([
            'is_banned'     => true,
            'banned_at'      => now(),
            'banned_reason'  => $request->get('reason'),
        ])->save();

        return back()->with('success', 'Kullanıcı banlandı.');
    }

    public function unban(Request $request, User $user)
    {
        $user->forceFill([
            'is_banned'     => false,
            'banned_at'      => null,
            'banned_reason'  => null,
        ])->save();

        return back()->with('success', 'Kullanıcı banı kaldırıldı.');
    }

    public function verifyPhoto(Request $request, User $user)
    {
        $user->forceFill([
            'is_verified'               => true,
            'photo_verify_status'        => 'approved',
            'photo_verify_reviewed_at'   => now(),
            'profile_verified_at'        => now(),
        ])->save();

        return back()->with('success', 'Fotoğraf doğrulandı. Mavi onay tik verildi.');
    }

    public function approve(Request $request, User $user)
    {
        $user->forceFill([
            'is_verified'               => true,
            'photo_verify_status'        => 'approved',
            'photo_verify_reviewed_at'   => now(),
            'profile_verified_at'        => now(),
        ])->save();

        return back()->with('success', 'Kullanıcı onaylandı. Mavi onay tik verildi.');
    }
}
