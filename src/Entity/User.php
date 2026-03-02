<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Il existe déjà un compte lié à cette email.')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank()]
    #[Assert\Length(
        min: 2,
        max: 150,
    )]
    #[ORM\Column(length: 150)]
    private ?string $nom = null; //////// NOM

    #[Assert\NotBlank()]
    #[Assert\Length(
        min: 2,
        max: 150,
    )]
    #[ORM\Column(length: 150)]
    private ?string $prenom = null; //////// PRENOM

    #[Assert\NotBlank()]
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
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $telephone = null; //////// TELEPHONE

    #[Assert\Email()]
    #[ORM\Column(length: 180)]
    private ?string $email = null; //////// EMAIL

    #[ORM\Column(length: 250)]
    private ?string $motPasse = null; //////// MOT DE PASSE

    #[ORM\Column]
    private ?bool $actif = null; //////// ACTIF?

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

    public function getMotPasse(): ?string
    {
        return $this->motPasse;
    }

    public function setMotPasse(string $motPasse): static
    {
        $this->motPasse = $motPasse;

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
}
