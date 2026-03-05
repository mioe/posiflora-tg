<?php

namespace App\Telegram;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TelegramClient implements TelegramClientInterface
{
    public function __construct(private HttpClientInterface $httpClient) {}

    public function sendMessage(string $botToken, string $chatId, string $text): void
    {
        $response = $this->httpClient->request('POST', "https://api.telegram.org/bot{$botToken}/sendMessage", [
            'json' => [
                'chat_id' => $chatId,
                'text' => $text,
            ],
        ]);

        $data = $response->toArray(false);

        if (!($data['ok'] ?? false)) {
            throw new \RuntimeException('Telegram API error: ' . ($data['description'] ?? 'unknown'));
        }
    }
}
