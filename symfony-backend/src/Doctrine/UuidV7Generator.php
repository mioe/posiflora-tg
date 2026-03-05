<?php

namespace App\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Symfony\Component\Uid\UuidV7;

class UuidV7Generator extends AbstractIdGenerator
{
    public function generateId(EntityManagerInterface $em, $entity): UuidV7
    {
        return new UuidV7();
    }
}
