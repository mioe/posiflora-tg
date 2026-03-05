<?php

namespace App\Repository;

use App\Entity\Shop;
use App\Entity\TelegramSendLog;
use App\Enum\SendStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TelegramSendLog>
 */
class TelegramSendLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramSendLog::class);
    }

    public function findLastByShop(Shop $shop): ?TelegramSendLog
    {
        return $this->findOneBy(['shop' => $shop], ['sentAt' => 'DESC']);
    }

    public function countByShopAndStatusSince(Shop $shop, SendStatus $status, \DateTimeImmutable $since): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.shop = :shop')
            ->andWhere('l.status = :status')
            ->andWhere('l.sentAt >= :since')
            ->setParameter('shop', $shop)
            ->setParameter('status', $status)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
