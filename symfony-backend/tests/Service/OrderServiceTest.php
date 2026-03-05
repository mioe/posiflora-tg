<?php

namespace App\Tests\Service;

use App\Entity\Order;
use App\Entity\Shop;
use App\Entity\TelegramIntegration;
use App\Entity\TelegramSendLog;
use App\Enum\SendStatus;
use App\Repository\TelegramIntegrationRepository;
use App\Repository\TelegramSendLogRepository;
use App\Service\OrderService;
use App\Telegram\TelegramClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private TelegramIntegrationRepository&MockObject $integrationRepo;
    private TelegramSendLogRepository&MockObject $sendLogRepo;
    private TelegramClientInterface&MockObject $telegramClient;
    private OrderService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->integrationRepo = $this->createMock(TelegramIntegrationRepository::class);
        $this->sendLogRepo = $this->createMock(TelegramSendLogRepository::class);
        $this->telegramClient = $this->createMock(TelegramClientInterface::class);

        $this->service = new OrderService(
            $this->em,
            $this->integrationRepo,
            $this->sendLogRepo,
            $this->telegramClient,
        );
    }

    public function testCreateOrderWithEnabledIntegrationSendsMessageAndLogsSent(): void
    {
        $shop = new Shop();
        $shop->setName('Test Shop');

        $integration = $this->makeIntegration($shop, enabled: true);

        $this->integrationRepo->method('findOneBy')->willReturn($integration);
        $this->sendLogRepo->method('findOneBy')->willReturn(null);

        $this->telegramClient->expects($this->once())->method('sendMessage');

        $persistedEntities = [];
        $this->em->method('persist')->willReturnCallback(
            function ($entity) use (&$persistedEntities): void { $persistedEntities[] = $entity; }
        );
        $this->em->method('flush');

        $result = $this->service->createOrder($shop, 'A-001', '1000', 'Иван');

        $this->assertSame('sent', $result['sendStatus']);
        $this->assertInstanceOf(Order::class, $result['order']);

        $logs = array_values(array_filter($persistedEntities, fn($e) => $e instanceof TelegramSendLog));
        $this->assertCount(1, $logs);
        $this->assertSame(SendStatus::SENT, $logs[0]->getStatus());
    }

    public function testIdempotencyPreventsDoubleSendAndNoDuplicateLog(): void
    {
        $shop = new Shop();
        $shop->setName('Test Shop');

        $integration = $this->makeIntegration($shop, enabled: true);

        $existingLog = new TelegramSendLog();
        $existingLog->setStatus(SendStatus::SENT);

        $this->integrationRepo->method('findOneBy')->willReturn($integration);
        $this->sendLogRepo->method('findOneBy')->willReturn($existingLog);

        $this->telegramClient->expects($this->never())->method('sendMessage');

        $persistedEntities = [];
        $this->em->method('persist')->willReturnCallback(
            function ($entity) use (&$persistedEntities): void { $persistedEntities[] = $entity; }
        );
        $this->em->method('flush');

        $result = $this->service->createOrder($shop, 'A-001', '1000', 'Иван');

        $this->assertSame('skipped', $result['sendStatus']);

        $logs = array_filter($persistedEntities, fn($e) => $e instanceof TelegramSendLog);
        $this->assertCount(0, $logs, 'No new TelegramSendLog should be created');
    }

    public function testTelegramFailureLogsFailedButOrderIsStillCreated(): void
    {
        $shop = new Shop();
        $shop->setName('Test Shop');

        $integration = $this->makeIntegration($shop, enabled: true);

        $this->integrationRepo->method('findOneBy')->willReturn($integration);
        $this->sendLogRepo->method('findOneBy')->willReturn(null);

        $this->telegramClient
            ->method('sendMessage')
            ->willThrowException(new \RuntimeException('Telegram timeout'));

        $persistedEntities = [];
        $this->em->method('persist')->willReturnCallback(
            function ($entity) use (&$persistedEntities): void { $persistedEntities[] = $entity; }
        );
        $this->em->method('flush');

        $result = $this->service->createOrder($shop, 'A-001', '1000', 'Иван');

        $this->assertSame('failed', $result['sendStatus']);
        $this->assertInstanceOf(Order::class, $result['order']);

        $logs = array_values(array_filter($persistedEntities, fn($e) => $e instanceof TelegramSendLog));
        $this->assertCount(1, $logs);
        $this->assertSame(SendStatus::FAILED, $logs[0]->getStatus());
        $this->assertSame('Telegram timeout', $logs[0]->getError());
    }

    private function makeIntegration(Shop $shop, bool $enabled): TelegramIntegration
    {
        $integration = new TelegramIntegration();
        $integration->setShop($shop);
        $integration->setBotToken('test-token');
        $integration->setChatId('12345');
        $integration->setEnabled($enabled);
        $integration->setCreatedAt(new \DateTimeImmutable());
        $integration->setUpdatedAt(new \DateTimeImmutable());

        return $integration;
    }
}
