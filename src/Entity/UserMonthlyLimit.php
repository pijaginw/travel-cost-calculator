<?php

namespace App\Entity;

use App\Repository\UserMonthlyLimitRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserMonthlyLimitRepository::class)]
#[ORM\Table(name: 'user_monthly_limits')]
class UserMonthlyLimit
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'monthlyLimits')]
    #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Id]
    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?DateTimeImmutable $usageMonth = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private ?int $uploadCount = 0;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getUsageMonth(): ?DateTimeImmutable
    {
        return $this->usageMonth;
    }

    public function setUsageMonth(DateTimeImmutable $usageMonth): static
    {
        $this->usageMonth = $usageMonth;

        return $this;
    }

    public function getUploadCount(): ?int
    {
        return $this->uploadCount;
    }

    public function setUploadCount(int $uploadCount): static
    {
        $this->uploadCount = $uploadCount;

        return $this;
    }
}
