<?php

namespace App\Entity;

use App\Repository\OutingRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OutingRepository::class)]
class Outing
{
    // Etat stocké en base
    public const ETAT_CREATION = 'creation';
    public const ETAT_PUBLIEE = 'publiee';
    public const ETAT_ANNULEE = 'annulee';

    // Etat obtenu par calcul
    public const ETAT_OUVERTE = 'ouverte';
    public const ETAT_CLOTUREE = 'cloturee';
    public const ETAT_EN_COURS = 'en_cours';
    public const ETAT_TERMINEE = 'terminee';
    public const ETAT_HISTORISEE = 'historisee';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(
        min: 5, minMessage: 'Le nom doit faire au moins {{ min }} caractères.',
        max: 180, maxMessage: 'Le nom ne doit pas dépasser {{ max }} caractères.',
    )]
    #[ORM\Column(length: 180)]
    private ?string $name = null;

    #[Assert\NotBlank(message: 'La date est obligatoire.')]
    #[ORM\Column]
    private ?\DateTimeImmutable $startDateTime = null;

    #[Assert\Positive]
    #[Assert\NotBlank(message: 'La durée est obligatoire.')]
    #[ORM\Column]
    private ?int $duration = null;

    #[Assert\NotBlank(message: "La date limite d'inscription est obligatoire.")]
    #[ORM\Column]
    private ?\DateTimeImmutable $registrationDeadline = null;

    #[Assert\Positive]
    #[Assert\NotBlank(message: 'Le nombre limite de participants est obligatoire.')]
    #[ORM\Column]
    private ?int $maxRegistrations = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $eventInfo = null;

    #[Assert\NotBlank(message: "L'état de la sortie est obligatoire")]
    #[ORM\Column(length: 50)]
    private ?string $status = null;  //Stock uniquement EN_CREATION

    #[Assert\NotNull]
    #[ORM\ManyToOne(inversedBy: 'outings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organizer = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne(inversedBy: 'outings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne(inversedBy: 'outings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Place $place = null;

    /**
     * @var Collection<int, Registration>
     */
    #[ORM\OneToMany(targetEntity: Registration::class, mappedBy: 'outing', orphanRemoval: true,cascade: ['persist', 'remove'])]
    private Collection $registrations;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdDateTime = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $cancelReason = null;

    public function __construct() {
        $this->status = self::ETAT_CREATION;
        $this->createdDateTime = new \DateTimeImmutable('now');
        $this->registrations = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStartDateTime(): ?\DateTimeImmutable
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(\DateTimeImmutable $startDateTime): static
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getRegistrationDeadline(): ?\DateTimeImmutable
    {
        return $this->registrationDeadline;
    }

    public function setRegistrationDeadline(\DateTimeImmutable $registrationDeadline): static
    {
        $this->registrationDeadline = $registrationDeadline;

        return $this;
    }


    public function getEventInfo(): ?string
    {
        return $this->eventInfo;
    }

    public function setEventInfo(?string $eventInfo): static
    {
        $this->eventInfo = $eventInfo;

        return $this;
    }

    public function getStatus(): string
    {
        if (in_array($this->status, [self::ETAT_CREATION, self::ETAT_ANNULEE]))
        {
            return $this->status;
        }

        $now = new \DateTimeImmutable();
        $start = $this->getStartDateTime();
        $limit = $this->getRegistrationDeadline();
        $end = (clone $start)->modify('+' . $this->getDuration() . ' minutes');
        $historisee = (clone $end)->modify('+1 month');

        if ($now < $limit && !$this->isMaxRegistrationsReached())
        {
            return self::ETAT_OUVERTE;
        }

        if ($this->isMaxRegistrationsReached()
            || $now >= $limit && $now < $start)
        {
            return self::ETAT_CLOTUREE;
        }

        if ($now >= $start && $now < $end)
        {
            return self::ETAT_EN_COURS;
        }

        if ($now >= $end && $now < $historisee)
        {
            return self::ETAT_TERMINEE;
        }

        return self::ETAT_HISTORISEE;
    }

    public function setStatus(string $newStatus): static
    {
        $this->status = $newStatus;

        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): static
    {
        $this->organizer = $organizer;

        return $this;
    }

    public function getMaxRegistrations(): ?int
    {
        return $this->maxRegistrations;
    }

    public function setMaxRegistrations(int $maxRegistrations): static
    {
        $this->maxRegistrations = $maxRegistrations;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): static
    {
        $this->place = $place;

        return $this;
    }

    /**
     * @return Collection<int, Registration>
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function addRegistration(Registration $registration): static
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations->add($registration);
            $registration->setOuting($this);
        }

        return $this;
    }

    public function removeRegistration(Registration $registration): static
    {
        if ($this->registrations->removeElement($registration))
        {
            if ($registration->getOuting() === $this)
            {
                $registration->setOuting(null);
            }
        }

        return $this;
    }

    public function getCreatedDateTime(): ?\DateTimeImmutable
    {
        return $this->createdDateTime;
    }

    public function setCreatedDateTime(\DateTimeImmutable $createdDateTime): static
    {
        $this->createdDateTime = $createdDateTime;

        return $this;
    }

    public function isPublished(): ?bool
    {
        if ($this->getStatus() === self::ETAT_PUBLIEE)
        {
            return true;
        }
        return false;
    }

    public function setPublished(): static
    {
        $this->status = self::ETAT_PUBLIEE;

        return $this;
    }

    public function isCancelled(): ?bool
    {
        if ($this->getStatus() === self::ETAT_ANNULEE)
        {
            return true;
        }
        return false;
    }

    public function setCancelled(): static
    {
        $this->status = self::ETAT_ANNULEE;

        return $this;
    }

    public function getCancelReason(): ?string
    {
        return $this->cancelReason;
    }

    public function setCancelReason(?string $cancelReason): static
    {
        $this->cancelReason = $cancelReason;

        return $this;
    }

    public function isRegistered(User $user): bool
    {
        foreach ($this->registrations as $registration)
        {
            if ($registration->getParticipant() === $user)
            {
                return true;
            }
        }
        
        return false;
    }

    public function getRegistrationFor(User $user): ?Registration
    {
        foreach ($this->registrations as $registration)
        {
            if ($registration->getParticipant() === $user)
            {
                return $registration;
            }
        }

        return null;
    }

    public function isOpen(): bool
    {
        if ($this->getStatus() === self::ETAT_OUVERTE)
        {
            return true;
        }
        return false;
    }

    public function isRegistrationDeadlinePassed(): bool
    {
        $now = new DateTimeImmutable();

        if ($this->getRegistrationDeadline() < $now)
        {
            return true;
        }
        return false;
    }

    public function isMaxRegistrationsReached(): bool
    {
        if ($this->getRegistrations()->count() >= $this->getMaxRegistrations())
        {
            return true;
        }
        return false;
    }

    public function isStarted(): bool
    {
        $now = new DateTimeImmutable();

        if ($this->getStartDateTime() <= $now)
        {
            return true;
        }
        return false;
    }
}
