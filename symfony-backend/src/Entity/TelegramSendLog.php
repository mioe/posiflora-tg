<?php

namespace App\Entity;

use App\Entity\Trait\InitUuidV7;
use App\Enum\SendStatus;
use App\Repository\TelegramSendLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TelegramSendLogRepository::class)]
#[ORM\UniqueConstraint(columns: ['shop_id', 'order_id'])]
class TelegramSendLog
{
    use InitUuidV7;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, columnDefinition: "UUID NOT NULL")]
    private ?Shop $shop = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, columnDefinition: "UUID NOT NULL")]
    private ?Order $order = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column(enumType: SendStatus::class)]
    private ?SendStatus $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $error = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $sentAt = null;

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): static
    {
        $this->shop = $shop;

        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;

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

    public function getStatus(): ?SendStatus
    {
        return $this->status;
    }

    public function setStatus(SendStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): static
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
