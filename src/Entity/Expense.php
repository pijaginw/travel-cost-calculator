<?php

namespace App\Entity;

use App\Repository\ExpenseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExpenseRepository::class)]
#[ORM\Table(name: 'expenses')]
#[ORM\Index(name: 'idx_expenses_trip_id', columns: ['trip_id'])]
#[ORM\HasLifecycleCallbacks]
class Expense
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'expenses')]
    #[ORM\JoinColumn(name: 'trip_id', nullable: false, onDelete: 'CASCADE')]
    private ?Trip $trip = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive(message: 'Expense amount must be positive.')]
    private ?string $amount = null;

    #[ORM\Column(type: 'string', enumType: ExpenseCategory::class, options: ['default' => 'Uncategorized'])]
    private ?ExpenseCategory $category = ExpenseCategory::Uncategorized;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrip(): ?Trip
    {
        return $this->trip;
    }

    public function setTrip(?Trip $trip): static
    {
        $this->trip = $trip;
        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function getCategory(): ?ExpenseCategory
    {
        return $this->category;
    }

    public function setCategory(ExpenseCategory $category): static
    {
        $this->category = $category;
        return $this;
    }
}
