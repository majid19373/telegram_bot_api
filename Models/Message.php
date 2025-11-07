<?php

namespace TelegramBot\Models;

final readonly class Message
{
    public function __construct(
        public int|string $chatId,
        public string     $text,
    )
    {
    }

}