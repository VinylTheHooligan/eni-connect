<?php

namespace App\Entity;

use App\Repository\OutingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OutingRepository::class)]
class Outing
{
    public const ETAT_CREATION = 'creation';
    public const ETAT_OUVERTE = 'ouverte';
    public const ETAT_CLOTUREE = 'cloturee';
    public const ETAT_EN_COURS = 'en_cours';
    public const ETAT_TERMINEE = 'terminee';
    public const ETAT_ANNULEE = 'annulee';
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
    private ?string $status = null;  //Permet de gérer les états : En création, Ouverte, Clôturée, Annulée et Archivé.

    #[Assert\NotNull]
    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organizer = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Place $place = null;

    /**
     * @var Collection<int, Registration>
     */
    #[ORM\OneToMany(targetEntity: Registration::class, mappedBy: 'outing', orphanRemoval: true,cascade: ['persist', 'remove'])]
    private Collection $registrations;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdDateTime = null;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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
        $this->registrations->removeElement($registration);
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
}
