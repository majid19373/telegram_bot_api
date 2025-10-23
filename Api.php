<?php

namespace TelegramBot;

use Exception;
use TelegramBot\Http\HttpClient;

final class Api
{
    private string $telegram_bot_url;
    private HttpClient $httpClient;

    public function __construct(string $token)
    {
        $this->telegram_bot_url = "https://api.telegram.org/bot$token/";
        $this->httpClient = HttpClient::baseUrl($this->telegram_bot_url)->acceptJson();
    }

    /**
     * @throws Exception
     */
    public function getMe(): array
    {
        return $this->httpClient->asJson()->post('getMe')->json();
    }

    /**
     * @throws Exception
     */
    public function getUpdates(): array
    {
        return $this->httpClient->asJson()->post('getUpdates')->json();
    }

    /**
     * @throws Exception
     */
    public function sendMessage(int|string $chatId, string $text): array
    {
        return $this->httpClient->asJson()->post('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
        ])->json();
    }

    /**
     * @throws Exception
     */
    public function sendPhoto(int|string $chatId, string $photo, ?string $caption = null): array
    {
        return $this->httpClient->asJson()->post('sendPhoto', [
            'chat_id' => $chatId,
            'photo' => $photo,
            'caption' => $caption,
        ])->json();
    }

    /**
     * @throws Exception
     */
    public function getUserProfilePhotos(int|string $userId): array
    {
        return $this->httpClient->asJson()->post('getUserProfilePhotos', [
            'user_id' => $userId,
        ])->json();
    }

    /**
     * @throws Exception
     */
    public function sendContact(int|string $chatId, string $phoneNumber, string $firstName, string $lastName): array
    {
        return $this->httpClient->asJson()->acceptJson()->post('sendContact', [
            'chat_id' => $chatId,
            'phone_number' => $phoneNumber,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ])->json();
    }

    /**
     * @throws Exception
     */
    public function sendPoll(int|string $chatId, string $question, array $options): array
    {
        return $this->httpClient->asJson()->acceptJson()->post('sendPoll', [
            'chat_id' => $chatId,
            'question' => $question,
            'options' => $options,
        ])->json();
    }


}