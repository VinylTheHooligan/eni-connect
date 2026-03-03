<?php

namespace App\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Repository\InscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[UniqueEntity(fields: ['participant', 'sortie'], message: 'Vous êtes déjà inscrit à cette sortie.')]
#[ORM\Entity(repositoryClass: InscriptionRepository::class)]
#[ORM\Table(
    name: 'inscription',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_participant_sortie', columns: ['participant_id', 'sortie_id'])
    ]
)]
class Inscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateInscription = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $participant = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sortie $sortie = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __construct() {
        $this->dateInscription = new \DateTimeImmutable();
    }

    public function getDateInscription(): ?\DateTimeImmutable
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeImmutable $dateInscription): static
    {
        $this->dateInscription = $dateInscription;

        return $this;
    }

    public function getParticipant(): ?User
    {
        return $this->participant;
    }

    public function setParticipant(?User $participant): static
    {
        $this->participant = $participant;

        return $this;
    }

    public function getSortie(): ?Sortie
    {
        return $this->sortie;
    }

    public function setSortie(?Sortie $sortie): static
    {
        $this->sortie = $sortie;

        return $this;
    }
}
