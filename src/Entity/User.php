<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(fields: ['email'], message: 'Il existe déjà un compte lié à cette email.')]
#[UniqueEntity(fields: ['username'], message: 'Il existe déjà un compte lié à ce pseudonyme.')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'uniq_user_email', columns: ['email'])]
#[ORM\UniqueConstraint(name: 'uniq_user_username', columns: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(
        min: 2, minMessage: 'Le nom doit faire au moins {{ min }} caractères.',
        max: 150, maxMessage: 'Le nom ne doit pas dépasser {{ max }} caractères.',
    )]
    #[ORM\Column(length: 150)]
    private ?string $lastName = null; ///////// NOM

    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    #[Assert\Length(
        min: 2, minMessage: 'Le prénom doit faire au moins {{ min }} caractères.',
        max: 150, maxMessage: 'Le prénom ne doit pas dépasser {{ max }} caractères.',
    )]
    #[ORM\Column(length: 150)]
    private ?string $firstName = null; //////// PRENOM

    #[Assert\NotBlank(message: 'Le pseudo est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9._-]+$/',
        message: 'Le pseudo ne peut contenir que des lettres, chiffres, ".", "_" et "-".'  )]
    #[Assert\Length(
        min: 3,
        max: 150,
    )]
    #[ORM\Column(length: 150)]
    private ?string $username = null; //////// PSEUDO

    #[Assert\Length(
        min: 10,
        max: 10,
    )]
    #[Assert\Regex(
        pattern: '/^\d{10}$/',
        message: 'Le téléphone doit contenir exactement 10 chiffres.',
    )]
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $phoneNumber = null; //////// TELEPHONE

    #[Assert\NotBlank(message: 'L’email est obligatoire.')]
    #[Assert\Email(message: 'L’email n’est pas valide.')]
    #[Assert\Length(max: 180, maxMessage: 'L’email ne doit pas dépasser {{ max }} caractères.')]
    #[ORM\Column(length: 180)]
    private ?string $email = null; //////// EMAIL

    #[ORM\Column(length: 250)]
    private ?string $passwordHash = null; //////// MOT DE PASSE HASHÉ

    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.', groups: ['registration'])]
    #[Assert\Length(
        min: 8,
        max: 50,
        minMessage: 'Le mot de passe doit faire au moins {{ min }} caractères.',
        maxMessage: 'Le mot de passe ne doit pas dépasser {{ max }} caractères.',
        groups: ['registration']
    )]
    private ?string $plainPassword = null; //////// MOT DE PASSE CLAIRE

    #[ORM\Column(type: 'json')]                     ///////// ROLES
    private array $roles = [];

    #[ORM\Column (options: ['default' => true])]
    private bool $isActive = true;

    /**
     * @var Collection<int, Outing>
     */
    #[ORM\OneToMany(targetEntity: Outing::class, mappedBy: 'organizer', fetch: 'LAZY')]
    private Collection $outings;

    /**
     * @var Collection<int, Registration>
     */
    #[ORM\OneToMany(targetEntity: Registration::class, mappedBy: 'participant', fetch: 'LAZY')]
    private Collection $registrations;

    #[ORM\ManyToOne(inversedBy: 'user', fetch: 'LAZY')]
    private ?Campus $campus = null;

    public function __construct()
    {
        $this->outings = new ArrayCollection();
        $this->registrations = new ArrayCollection();
    } //////// ACTIF?

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profilePicture = null;

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): static
    {
        $this->profilePicture = $profilePicture;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): static
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, Outing>
     */
    public function getOutings(): Collection
    {
        return $this->outings;
    }

    public function addOuting(Outing $outing): static
    {
        if (!$this->outings->contains($outing)) {
            $this->outings->add($outing);
            $outing->setOrganizer($this);
        }

        return $this;
    }

    public function removeOuting(Outing $outing): static
    {
        $this->outings->removeElement($outing);
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
            $registration->setParticipant($this);
        }

        return $this;
    }

    public function removeRegistration(Registration $registration): static
    {
        $this->registrations->removeElement($registration);
        return $this;
    }

    public function eraseCredentials(): void
    {

    }

    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
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
}
