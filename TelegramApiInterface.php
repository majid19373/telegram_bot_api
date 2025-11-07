<?php

namespace TelegramBot;

use Exception;
use TelegramBot\Models\Message;
use TelegramBot\Models\Photo;
use TelegramBot\Models\SentMessage;
use TelegramBot\Models\User;

interface TelegramApiInterface
{
    /**
     * @throws Exception
     */
    public function getMe(): User;

    /**
     * @throws Exception
     */
    public function sendMessage(Message $message): SentMessage;

    /**
     * @throws Exception
     */
    public function sendPhoto(Photo $photo): SentMessage;
}