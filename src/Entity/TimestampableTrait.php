<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A reusable trait for entities that need created_at and updated_at timestamps.
 * Uses Doctrine's lifecycle callbacks to automatically manage these fields.
 */
trait TimestampableTrait
{
    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?DateTimeImmutable $updatedAt = null;

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
