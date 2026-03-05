<?php

namespace App\Controller;

use App\Repository\ShopRepository;
use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/shops/{shopId}/orders')]
class OrderController extends AbstractController
{
    public function __construct(
        private ShopRepository $shopRepo,
        private OrderService $orderService,
    ) {}

    #[Route('', methods: ['POST'])]
    public function create(string $shopId, Request $request): JsonResponse
    {
        $shop = $this->shopRepo->find($shopId);
        if (!$shop) {
            return $this->json(['error' => 'Shop not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->toArray();
        $number = trim($data['number'] ?? '');
        $total = trim((string) ($data['total'] ?? ''));
        $customerName = trim($data['customerName'] ?? '');

        if ($number === '' || $total === '' || $customerName === '') {
            return $this->json(['error' => 'number, total and customerName are required'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $result = $this->orderService->createOrder($shop, $number, $total, $customerName);
        $order = $result['order'];

        return $this->json([
            'order' => [
                'id' => (string) $order->getId(),
                'shopId' => (string) $shop->getId(),
                'number' => $order->getNumber(),
                'total' => $order->getTotal(),
                'customerName' => $order->getCustomName(),
                'createdAt' => $order->getCreatedAt()->format(\DateTimeInterface::ATOM),
            ],
            'sendStatus' => $result['sendStatus'],
        ], Response::HTTP_CREATED);
    }
}
