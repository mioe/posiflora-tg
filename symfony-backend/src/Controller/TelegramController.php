<?php

namespace App\Controller;

use App\Repository\ShopRepository;
use App\Service\TelegramIntegrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/shops/{shopId}/telegram')]
class TelegramController extends AbstractController
{
    public function __construct(
        private ShopRepository $shopRepo,
        private TelegramIntegrationService $integrationService,
    ) {}

    #[Route('/connect', methods: ['POST'])]
    public function connect(string $shopId, Request $request): JsonResponse
    {
        $shop = $this->shopRepo->find($shopId);
        if (!$shop) {
            return $this->json(['error' => 'Shop not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->toArray();
        $botToken = trim($data['botToken'] ?? '');
        $chatId = trim($data['chatId'] ?? '');
        $enabled = (bool) ($data['enabled'] ?? true);

        if ($botToken === '' || $chatId === '') {
            return $this->json(['error' => 'botToken and chatId are required'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $integration = $this->integrationService->connect($shop, $botToken, $chatId, $enabled);

        return $this->json([
            'id' => (string) $integration->getId(),
            'shopId' => (string) $shop->getId(),
            'botToken' => substr($integration->getBotToken(), 0, 10) . '...',
            'chatId' => $integration->getChatId(),
            'enabled' => $integration->isEnabled(),
            'createdAt' => $integration->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $integration->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ]);
    }

    #[Route('/status', methods: ['GET'])]
    public function status(string $shopId): JsonResponse
    {
        $shop = $this->shopRepo->find($shopId);
        if (!$shop) {
            return $this->json(['error' => 'Shop not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->integrationService->getStatus($shop));
    }
}
