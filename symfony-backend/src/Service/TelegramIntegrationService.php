<?php

namespace App\Service;

use App\Entity\Shop;
use App\Entity\TelegramIntegration;
use App\Enum\SendStatus;
use App\Repository\TelegramIntegrationRepository;
use App\Repository\TelegramSendLogRepository;
use Doctrine\ORM\EntityManagerInterface;

class TelegramIntegrationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TelegramIntegrationRepository $integrationRepo,
        private TelegramSendLogRepository $sendLogRepo,
    ) {}

    public function connect(Shop $shop, string $botToken, string $chatId, bool $enabled): TelegramIntegration
    {
        $integration = $this->integrationRepo->findOneBy(['shop' => $shop]);

        if (!$integration) {
            $integration = new TelegramIntegration();
            $integration->setShop($shop);
            $integration->setCreatedAt(new \DateTimeImmutable());
        }

        $integration->setBotToken($botToken);
        $integration->setChatId($chatId);
        $integration->setEnabled($enabled);
        $integration->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($integration);
        $this->em->flush();

        return $integration;
    }

    public function getStatus(Shop $shop): array
    {
        $integration = $this->integrationRepo->findOneBy(['shop' => $shop]);

        if (!$integration) {
            return [
                'enabled' => false,
                'chatId' => null,
                'lastSentAt' => null,
                'sentCount' => 0,
                'failedCount' => 0,
            ];
        }

        $since = new \DateTimeImmutable('-7 days');
        $lastLog = $this->sendLogRepo->findLastByShop($shop);

        return [
            'enabled' => $integration->isEnabled(),
            'chatId' => $this->maskChatId($integration->getChatId()),
            'lastSentAt' => $lastLog?->getSentAt()?->format(\DateTimeInterface::ATOM),
            'sentCount' => $this->sendLogRepo->countByShopAndStatusSince($shop, SendStatus::SENT, $since),
            'failedCount' => $this->sendLogRepo->countByShopAndStatusSince($shop, SendStatus::FAILED, $since),
        ];
    }

    private function maskChatId(string $chatId): string
    {
        if (strlen($chatId) <= 4) {
            return str_repeat('*', strlen($chatId));
        }

        return substr($chatId, 0, 2) . str_repeat('*', strlen($chatId) - 4) . substr($chatId, -2);
    }
}
