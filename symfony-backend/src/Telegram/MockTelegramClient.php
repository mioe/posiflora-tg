<?php

namespace App\Telegram;

use Psr\Log\LoggerInterface;

class MockTelegramClient implements TelegramClientInterface
{
    public function __construct(private LoggerInterface $logger) {}

    public function sendMessage(string $botToken, string $chatId, string $text): void
    {
        $this->logger->info('[MOCK] Telegram message sent', [
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }
}
