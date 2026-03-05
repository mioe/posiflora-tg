<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\Shop;
use App\Entity\TelegramSendLog;
use App\Enum\SendStatus;
use App\Repository\TelegramIntegrationRepository;
use App\Repository\TelegramSendLogRepository;
use App\Telegram\TelegramClientInterface;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TelegramIntegrationRepository $integrationRepo,
        private TelegramSendLogRepository $sendLogRepo,
        private TelegramClientInterface $telegramClient,
    ) {}

    public function createOrder(Shop $shop, string $number, string $total, string $customerName): array
    {
        $order = new Order();
        $order->setShop($shop);
        $order->setNumber($number);
        $order->setTotal($total);
        $order->setCustomName($customerName);
        $order->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($order);
        $this->em->flush();

        $sendStatus = $this->sendTelegramNotification($shop, $order, $number, $total, $customerName);

        return ['order' => $order, 'sendStatus' => $sendStatus];
    }

    private function sendTelegramNotification(
        Shop $shop,
        Order $order,
        string $number,
        string $total,
        string $customerName,
    ): string {
        $integration = $this->integrationRepo->findOneBy(['shop' => $shop]);

        if (!$integration || !$integration->isEnabled()) {
            return 'skipped';
        }

        $existingLog = $this->sendLogRepo->findOneBy(['shop' => $shop, 'order' => $order]);
        if ($existingLog) {
            return 'skipped';
        }

        $message = sprintf('Новый заказ %s на сумму %s ₽, клиент %s', $number, $total, $customerName);

        $log = new TelegramSendLog();
        $log->setShop($shop);
        $log->setOrder($order);
        $log->setMessage($message);
        $log->setSentAt(new \DateTimeImmutable());

        try {
            $this->telegramClient->sendMessage(
                $integration->getBotToken(),
                $integration->getChatId(),
                $message,
            );
            $log->setStatus(SendStatus::SENT);
            $sendStatus = 'sent';
        } catch (\Throwable $e) {
            $log->setStatus(SendStatus::FAILED);
            $log->setError($e->getMessage());
            $sendStatus = 'failed';
        }

        $this->em->persist($log);
        $this->em->flush();

        return $sendStatus;
    }
}
