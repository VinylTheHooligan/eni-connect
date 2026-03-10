<?php

namespace App\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Repository\RegistrationRepository;
use Doctrine\ORM\Mapping as ORM;

#[UniqueEntity(fields: ['participant', 'outing'], message: 'Vous êtes déjà inscrit à cette sortie.')]
#[ORM\Entity(repositoryClass: RegistrationRepository::class)]
#[ORM\Table(
    name: 'registration',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_participant_sortie', columns: ['participant_id', 'sortie_id'])
    ]
)]
class Registration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $registrationDate = null;

    #[ORM\ManyToOne(inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $participant = null;

    #[ORM\ManyToOne(inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Outing $outing = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __construct() {
        $this->registrationDate = new \DateTimeImmutable();
    }

    public function getRegistrationDate(): ?\DateTimeImmutable
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(\DateTimeImmutable $registrationDate): static
    {
        $this->registrationDate = $registrationDate;

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

    public function getOuting(): ?Outing
    {
        return $this->outing;
    }

    public function setOuting(?Outing $outing): static
    {
        $this->outing = $outing;

        return $this;
    }
}
