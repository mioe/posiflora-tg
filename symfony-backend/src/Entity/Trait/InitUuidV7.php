<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use App\Doctrine\UuidV7Generator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\UuidV7;

trait InitUuidV7
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    private UuidV7 $id;

    public function getId(): UuidV7
    {
        return $this->id;
    }
}
