<?php

namespace App\Controller;

use App\Repository\ShopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/shops')]
class ShopController extends AbstractController
{
    public function __construct(private ShopRepository $shopRepo) {}

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $shops = $this->shopRepo->findAll();

        return $this->json(array_map(fn($shop) => [
            'id' => (string) $shop->getId(),
            'name' => $shop->getName(),
        ], $shops));
    }
}
