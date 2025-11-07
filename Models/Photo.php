<?php

namespace TelegramBot\Models;

final readonly class Photo
{
    public function __construct(
        public int|string $chatId,
        public string     $photo,
        public ?string    $caption,
    )
    {
    }

}