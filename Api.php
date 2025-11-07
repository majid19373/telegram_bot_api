<?php

namespace TelegramBot;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use TelegramBot\Models\Message;
use TelegramBot\Models\Photo;
use TelegramBot\Models\SentMessage;
use TelegramBot\Models\User;

final class Api implements TelegramApiInterface
{
    private string                  $telegram_bot_url;
    private ClientInterface         $httpClient;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface  $streamFactory;

    public function __construct(
        string                  $token,
        ClientInterface         $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface  $streamFactory)
    {
        $this->telegram_bot_url = "https://api.telegram.org/bot$token/";
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    public function getMe(): User
    {
        $result = $this->sendRequest('getMe');

        return new User(
            id:        $result['id'],
            isBot:     $result['is_bot'],
            firstName: $result['first_name'],
            lastName:  $result['last_name'] ?? null,
            username:  $result['username'] ?? null,
        );
    }

    /**
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    public function sendMessage(Message $message): SentMessage
    {
        $result = $this->sendRequest('sendMessage', [
            'chat_id' => $message->chatId,
            'text' => $message->text
        ]);

        return $this->sentMessage($result);
    }

    /**
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    public function sendPhoto(Photo $photo): SentMessage
    {
        $result = $this->sendRequest('sendPhoto', [
            'chat_id' => $photo->chatId,
            'photo' => $photo->photo,
            'caption' => $photo->caption,
        ]);

        return $this->sentMessage($result);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    private function sendRequest(string $method, array $data = []): array
    {
        $url = $this->telegram_bot_url . $method;

        $body = json_encode($data);
        if ($body === false) {
            throw new Exception('Failed to encode JSON data');
        }

        $request = $this->requestFactory->createRequest('POST', $url)
                                        ->withHeader('Content-Type', 'application/json')
                                        ->withHeader('Accept', 'application/json')
                                        ->withBody($this->streamFactory->createStream($body));

        $response = $this->httpClient->sendRequest($request);

        $responseBody = (string)$response->getBody();
        $decoded = json_decode($responseBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to decode JSON response: ' . json_last_error_msg());
        }

        if (!isset($decoded['ok']) || $decoded['ok'] !== true) {
            $errorMessage = $decoded['description'] ?? 'Unknown error';
            throw new Exception("Telegram API error: $errorMessage");
        }

        return $decoded['result'];
    }

    private function sentMessage(array $result): SentMessage
    {
        $user = null;
        if ($result['from']) {
            $from = $result['from'];
            $user = new User(
                id:        $from['id'],
                isBot:     $from['is_bot'],
                firstName: $from['first_name'],
                lastName:  $from['last_name'] ?? null,
                username:  $from['username'] ?? null,
            );
        }
        return new SentMessage(
            messageId:       $result['message_id'],
            messageThreadId: $result['message_thread_id'] ?? null,
            from:            $user,
        );
    }


}