<?php

namespace App\Support;

class ChatMessageHelper
{
    public static function isEmojiOnly(string $text): bool
    {
        $trimmed = trim($text);

        if ($trimmed === '') {
            return false;
        }

        $withoutEmoji = preg_replace('/[\p{Extended_Pictographic}\p{Emoji_Presentation}\s]+/u', '', $trimmed);

        return $withoutEmoji === '';
    }

    /** @return string[] */
    public static function reactionEmojis(): array
    {
        return [
            '❤️', '💕', '💗', '💖', '💝', '💘', '💍', '👰', '🤵',
            '🥂', '✨', '🌹', '💐', '😍', '🥰', '😘', '🎉', '💑',
            '🕊️', '☺️', '😊', '🙏', '💫', '🤗', '😻', '💌', '🌸',
        ];
    }
}

