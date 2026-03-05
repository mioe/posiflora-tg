<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\Shop;
use App\Entity\TelegramIntegration;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $shop = new Shop();
        $shop->setName('Posiflora Demo');
        $manager->persist($shop);

        $integration = new TelegramIntegration();
        $integration->setShop($shop);
        $integration->setBotToken('123456789:AABBCCDDEEFFaabbccddeeff-demo-token');
        $integration->setChatId('987654321');
        $integration->setEnabled(true);
        $integration->setCreatedAt(new \DateTimeImmutable('-10 days'));
        $integration->setUpdatedAt(new \DateTimeImmutable('-10 days'));
        $manager->persist($integration);

        $orders = [
            ['A-1001', '1200.00', 'Анна'],
            ['A-1002', '3450.50', 'Борис'],
            ['A-1003', '780.00', 'Виктория'],
            ['A-1004', '5600.00', 'Григорий'],
            ['A-1005', '2490.00', 'Дарья'],
            ['A-1006', '990.00', 'Евгений'],
            ['A-1007', '1850.00', 'Жанна'],
        ];

        foreach ($orders as $i => [$number, $total, $customer]) {
            $order = new Order();
            $order->setShop($shop);
            $order->setNumber($number);
            $order->setTotal($total);
            $order->setCustomName($customer);
            $order->setCreatedAt(new \DateTimeImmutable("-{$i} days"));
            $manager->persist($order);
        }

        $manager->flush();
    }
}
