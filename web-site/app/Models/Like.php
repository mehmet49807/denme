<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'post_id'];

    protected static function booted(): void
    {
        static::created(function (Like $like) {
            $like->post()->increment('likes_count');

            dispatch(function () use ($like) {
                try {
                    app(\App\Services\NotificationService::class)->notifyPostLiked($like);
                } catch (\Throwable) {
                    //
                }

                try {
                    $post = $like->relationLoaded('post') ? $like->post : Post::find($like->post_id);
                    if ($post) {
                        // Refresh likes_count for payload accuracy.
                        $post->likes_count = (int) $post->likes_count;
                        app(\App\Services\RealtimeBroadcastService::class)->postUpdated($post);
                    }
                } catch (\Throwable) {
                    // Realtime opsiyonel.
                }
            })->afterResponse();
        });

        static::deleted(function (Like $like) {
            $like->post()->decrement('likes_count');

            dispatch(function () use ($like) {
                try {
                    $post = Post::find($like->post_id);
                    if ($post) {
                        app(\App\Services\RealtimeBroadcastService::class)->postUpdated($post);
                    }
                } catch (\Throwable) {
                    //
                }
            })->afterResponse();
        });
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

