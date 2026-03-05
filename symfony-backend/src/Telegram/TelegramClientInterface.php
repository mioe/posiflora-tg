<?php

namespace App\Telegram;

interface TelegramClientInterface
{
    /**
     * @throws \RuntimeException when sending fails
     */
    public function sendMessage(string $botToken, string $chatId, string $text): void;
}
