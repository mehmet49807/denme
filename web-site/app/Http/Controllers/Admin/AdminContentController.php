<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Story;
use Illuminate\Http\Request;

class AdminContentController extends Controller
{
    public function posts(Request $request)
    {
        $query = Post::with('user');

        $status = $request->get('status');
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $posts = $query->latest()->paginate(30);

        return view('admin.content.posts', compact('posts'));
    }

    public function destroyPost(Request $request, Post $post)
    {
        $post->delete();

        return back()->with('success', 'Gönderi silindi.');
    }

    public function stories(Request $request)
    {
        $stories = Story::with('user')
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->paginate(30);

        return view('admin.content.stories', compact('stories'));
    }

    public function destroyStory(Request $request, Story $story)
    {
        $story->delete();

        return back()->with('success', 'Hikaye silindi.');
    }
}
