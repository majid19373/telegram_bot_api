<?php

namespace TelegramBot\Models;

final readonly class SentMessage implements ToArrayInterface
{
    public function __construct(
        public int   $messageId,
        public ?int  $messageThreadId,
        public ?User $from,
    )
    {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}