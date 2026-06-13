<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home(): View
    {
        return view('web.home');
    }

    public function feed(Request $request): View
    {
        $user = $request->user();

        $posts = Post::with('user')
            ->whereHas('user', fn ($q) => $q->where('gender', $user->oppositeGender())->where('status', 'active'))
            ->whereNotIn('user_id', $user->blockedUserIds())
            ->latest()
            ->paginate(15);

        return view('web.feed', compact('posts'));
    }

    public function profile(Request $request): View
    {
        return view('web.profile', ['user' => $request->user()]);
    }
}
