<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie
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
    private ?string $nom = null;

    #[Assert\NotBlank(message: 'La date est obligatoire.')]
    #[ORM\Column]
    private ?\DateTimeImmutable $dateHeureDebut = null;

    #[Assert\Positive]
    #[Assert\NotBlank(message: 'La durée est obligatoire.')]
    #[ORM\Column]
    private ?int $duree = null;

    #[Assert\NotBlank(message: "La date limite d'inscription est obligatoire.")]
    #[ORM\Column]
    private ?\DateTimeImmutable $dateLimiteInscription = null;

    #[Assert\Positive]
    #[Assert\NotBlank(message: 'Le nombre limite de participants est obligatoire.')]
    #[ORM\Column]
    private ?int $nbInscriptionsMax = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $infosSortie = null;

    #[Assert\NotBlank(message: "L'état de la sortie est obligatoire")]
    #[ORM\Column(length: 50)]
    private ?string $etat = null;  //Permet de gérer les états : En création, Ouverte, Clôturée, Annulée et Archivé.

    #[Assert\NotNull]
    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organisateur = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lieu $lieu = null;

    /**
     * @var Collection<int, Inscription>
     */
    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'sortie', orphanRemoval: true,cascade: ['persist', 'remove'])]
    private Collection $inscriptions;

    public function __construct() {
        $this->etat = self::ETAT_CREATION;
        $this->dateHeureDebut = new \DateTimeImmutable();
        $this->inscriptions = new ArrayCollection();
    }
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

    public function getDateHeureDebut(): ?\DateTimeImmutable
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeImmutable $dateHeureDebut): static
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeImmutable
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeImmutable $dateLimiteInscription): static
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }


    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(?string $infosSortie): static
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getOrganisateur(): ?User
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?User $organisateur): static
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    public function getNbInscriptionsMax(): ?int
    {
        return $this->nbInscriptionsMax;
    }

    public function setNbInscriptionsMax(int $nbInscriptionsMax): static
    {
        $this->nbInscriptionsMax = $nbInscriptionsMax;

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

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): static
    {
        $this->lieu = $lieu;

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
            $inscription->setSortie($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): static
    {
        $this->inscriptions->removeElement($inscription);
        return $this;
    }
}
