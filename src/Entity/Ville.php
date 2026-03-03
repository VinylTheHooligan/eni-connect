<?php

namespace App\Entity;

use App\Repository\VilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use OutOfRangeException;

#[ORM\Entity(repositoryClass: VilleRepository::class)]
class Ville
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Length(
        min: 1, minMessage: 'La ville doit contenir au minimum {{ min }} caractère.',
        max: 50, maxMessage: 'La ville doit contenir au maximum {{ max }} caractères.',
    )]
    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[Assert\NotBlank(message: 'Le code postal obligatoire.')]
    #[Assert\Range(
        min: 5,
        max: 5,
        notInRangeMessage: 'Le code postal doit contenir précisément {{ max }} chiffres.',
    )]
    #[ORM\Column(length: 5)]
    private ?string $codePostal = null;

    /**
     * @var Collection<int, Lieu>
     */
    #[Assert\NotBlank(message: 'La "ville" doit être obligatoire.')]
    #[ORM\OneToMany(targetEntity: Lieu::class, mappedBy: 'ville')]
    private Collection $lieux;

    public function __construct()
    {
        $this->lieux = new ArrayCollection();
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

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * @return Collection<int, Lieu>
     */
    public function getLieux(): Collection
    {
        return $this->lieux;
    }

    public function addLieux(Lieu $lieux): static
    {
        if (!$this->lieux->contains($lieux)) {
            $this->lieux->add($lieux);
            $lieux->setVille($this);
        }

        return $this;
    }

    public function removeLieux(Lieu $lieux): static
    {
        $this->lieux->removeElement($lieux);
        return $this;
    }
}
