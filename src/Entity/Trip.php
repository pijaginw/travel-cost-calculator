<?php

namespace App\Entity;

use App\Repository\TripRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TripRepository::class)]
#[ORM\Table(name: 'trips')]
#[ORM\UniqueConstraint(name: 'uq_user_trip_name', columns: ['user_id', 'trip_name'])]
#[ORM\Index(name: 'idx_trips_user_id', columns: ['user_id'])]
#[ORM\HasLifecycleCallbacks]
class Trip
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;  // @phpstan-ignore-line

    #[ORM\ManyToOne(inversedBy: 'trips')]
    #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 70)]
    #[Assert\NotBlank(message: 'Trip name cannot be empty.')]
    #[Assert\Length(max: 70)]
    private ?string $tripName = null;

    #[ORM\Column(length: 3)]
    #[Assert\NotBlank]
    #[Assert\Currency]
    private ?string $tripCurrency = null;

    /**
     * @var Collection<int, Expense>
     */
    #[ORM\OneToMany(targetEntity: Expense::class, mappedBy: 'trip', cascade: ['remove'], orphanRemoval: true)]
    private Collection $expenses;

    public function __construct()
    {
        $this->expenses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTripName(): ?string
    {
        return $this->tripName;
    }

    public function setTripName(string $tripName): static
    {
        $this->tripName = $tripName;

        return $this;
    }

    public function getTripCurrency(): ?string
    {
        return $this->tripCurrency;
    }

    public function setTripCurrency(string $tripCurrency): static
    {
        $this->tripCurrency = $tripCurrency;

        return $this;
    }

    /**
     * @return Collection<int, Expense>
     */
    public function getExpenses(): Collection
    {
        return $this->expenses;
    }
}
