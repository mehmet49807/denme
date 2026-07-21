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
            app(\App\Services\NotificationService::class)->notifyPostLiked($like);

            $post = Post::find($like->post_id);
            if ($post) {
                try {
                    app(\App\Services\RealtimeBroadcastService::class)->postUpdated($post);
                } catch (\Throwable) {
                    // Realtime opsiyonel; beğeni kaydı bozulmasın.
                }
            }
        });

        static::deleted(function (Like $like) {
            $like->post()->decrement('likes_count');

            $post = Post::find($like->post_id);
            if ($post) {
                try {
                    app(\App\Services\RealtimeBroadcastService::class)->postUpdated($post);
                } catch (\Throwable) {
                    //
                }
            }
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

