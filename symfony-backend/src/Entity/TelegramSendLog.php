<?php

namespace App\Entity;

use App\Entity\Trait\InitUuidV7;
use App\Repository\TelegramSendLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TelegramSendLogRepository::class)]
class TelegramSendLog
{
    use InitUuidV7;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, columnDefinition: "UUID NOT NULL")]
    private ?Shop $shop = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, columnDefinition: "UUID NOT NULL")]
    private ?Order $shopOrder = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column(length: 10)]
    private ?string $status = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $error = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $sentAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): static
    {
        $this->shop = $shop;

        return $this;
    }

    public function getShopOrder(): ?Order
    {
        return $this->shopOrder;
    }

    public function setShopOrder(?Order $shopOrder): static
    {
        $this->shopOrder = $shopOrder;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(string $error): static
    {
        $this->error = $error;

        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTimeImmutable $sentAt): static
    {
        $this->sentAt = $sentAt;

        return $this;
    }
}
