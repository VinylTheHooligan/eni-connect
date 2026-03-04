<?php

namespace App\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Repository\CampusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(fields: ['name'], message: 'Ce campus existe déjà.')]
#[ORM\Entity(repositoryClass: CampusRepository::class)]
class Campus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[Assert\Length(
        min: 3, minMessage: 'Le nom doit contenir au minimum {{ min }} caractères.',
        max: 50, maxMessage: 'Le nom doit contenir au maximum {{ max }} caractères.',
    )]
    #[ORM\Column(length: 50)]
    private ?string $name = null;

    /**
     * @var Collection<int, Outing>
     */
    #[ORM\OneToMany(targetEntity: Outing::class, mappedBy: 'campus')]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
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

    /**
     * @return Collection<int, Outing>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Outing $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setCampus($this);
        }

        return $this;
    }

    public function removeEvent(Outing $event): static
    {
        $this->events->removeElement($event);
        return $this;
    }
}
