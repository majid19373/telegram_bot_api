<?php

namespace TelegramBot\Models;

final readonly class User implements ToArrayInterface
{
    public function __construct(
        public int     $id,
        public bool    $isBot,
        public string  $firstName,
        public ?string $lastName,
        public ?string $username,
    )
    {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

}