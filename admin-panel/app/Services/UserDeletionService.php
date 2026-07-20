<?php

namespace App\Services;

use App\Models\User;
use InvalidArgumentException;

class UserDeletionService
{
    public function __construct(
        private MediaUploadService $mediaUpload,
    ) {}

    public function delete(User $user): void
    {
        if ($user->isAdmin()) {
            throw new InvalidArgumentException('Yönetici hesapları silinemez.');
        }

        $this->mediaUpload->deleteByUrl($user->profile_photo_url);

        $user->loadMissing(['posts', 'stories']);

        foreach ($user->posts as $post) {
            $this->mediaUpload->deleteByUrl($post->image_url);
        }

        foreach ($user->stories as $story) {
            $this->mediaUpload->deleteByUrl($story->media_url);
        }

        $user->tokens()->delete();
        $user->delete();
    }
}
