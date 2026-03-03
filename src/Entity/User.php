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
#[UniqueEntity(fields: ['pseudo'], message: 'Il existe déjà un compte lié à ce pseudonyme.')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_user_email', columns: ['email']),
    new ORM\UniqueConstraint(name: 'uniq_user_pseudo', columns: ['pseudo']),
])]
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
    private ?string $nom = null; ///////// NOM

    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    #[Assert\Length(
        min: 2, minMessage: 'Le prénom doit faire au moins {{ min }} caractères.',
        max: 150, maxMessage: 'Le prénom ne doit pas dépasser {{ max }} caractères.',
    )]
    #[ORM\Column(length: 150)]
    private ?string $prenom = null; //////// PRENOM

    #[Assert\NotBlank(message: 'Le pseudo est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9._-]+$/',
        message: 'Le pseudo ne peut contenir que des lettres, chiffres, ".", "_" et "-".'  )]
    #[Assert\Length(
        min: 3,
        max: 150,
    )]
    #[ORM\Column(length: 150)]
    private ?string $pseudo = null; //////// PSEUDO

    #[Assert\Length(
        min: 10,
        max: 10,
    )]
    #[Assert\Regex(
        pattern: '/^\d{10}$/',
        message: 'Le téléphone doit contenir exactement 10 chiffres.',
    )]
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $telephone = null; //////// TELEPHONE

    #[Assert\NotBlank(message: 'L’email est obligatoire.')]
    #[Assert\Email(message: 'L’email n’est pas valide.')]
    #[Assert\Length(max: 180, maxMessage: 'L’email ne doit pas dépasser {{ max }} caractères.')]
    #[ORM\Column(length: 180)]
    private ?string $email = null; //////// EMAIL

    #[ORM\Column(length: 250)]
    private ?string $hashMotPasse = null; //////// MOT DE PASSE HASHÉ

    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.', groups: ['inscription'])]
    #[Assert\Length(
        min: 8,
        max: 50,
        minMessage: 'Le mot de passe doit faire au moins {{ min }} caractères.',
        maxMessage: 'Le mot de passe ne doit pas dépasser {{ max }} caractères.',
        groups: ['inscription']
    )]
    private ?string $motPasse = null; //////// MOT DE PASSE CLAIRE

    #[ORM\Column(type: 'json')]                     ///////// ROLES
    private array $roles = [];
    
    #[ORM\Column (options: ['default' => true])]
    private bool $actif = true;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'organisateur')]
    private Collection $sorties;

    /**
     * @var Collection<int, Inscription>
     */
    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'participant')]
    private Collection $inscriptions;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
        $this->inscriptions = new ArrayCollection();
    } //////// ACTIF?

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

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

    public function getHashMotPasse(): ?string
    {
        return $this->hashMotPasse;
    }

    public function setHashMotPasse(string $hashMotPasse): static
    {
        $this->hashMotPasse = $hashMotPasse;

        return $this;
    }

    public function getMotPasse(): ?string
    {
        return $this->motPasse;
    }

    public function setMotPasse(?string $motPasse): static
    {
        $this->motPasse = $motPasse;
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

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): static
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties->add($sorty);
            $sorty->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): static
    {
        $this->sorties->removeElement($sorty);
        return $this;
    }

    /**
     * @return Collection<int, Inscription>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(Inscription $inscription): static
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
            $inscription->setParticipant($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): static
    {
        $this->inscriptions->removeElement($inscription);
        return $this;
    }

    public function eraseCredentials(): void
    {
        
    }

    public function getPassword(): ?string
    {
        return $this->motPasse;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
