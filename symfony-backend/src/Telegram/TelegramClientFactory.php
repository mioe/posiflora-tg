<?php

namespace App\Telegram;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TelegramClientFactory
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private bool $mock,
    ) {}

    public function create(): TelegramClientInterface
    {
        if ($this->mock) {
            return new MockTelegramClient($this->logger);
        }

        return new TelegramClient($this->httpClient);
    }
}
