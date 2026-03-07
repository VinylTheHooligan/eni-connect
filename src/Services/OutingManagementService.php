<?php

namespace App\Services;

use App\Entity\Outing;
use App\Entity\Registration;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;
use LogicException;

class OutingManagementService
{
    public function __construct(
        private EntityManagerInterface $em
    )
    {}

    public function initializeOuting(User $organizer, bool $isAdmin): Outing
    {
        $outing = new Outing();

        if (!$isAdmin)
        {
            $outing->setOrganizer($organizer);
            $outing->setCampus($organizer->getCampus());
        }

        return $outing;
    }

    public function publish(Outing $outing): void
    {
        if ($outing->getStatus() !== Outing::ETAT_CREATION)
        {
            throw new LogicException("Impossible de publier une sortie qui n'est pas en création");
        }

        $outing->setStatus(Outing::ETAT_OUVERTE);
        $outing->setPublished(true);
    }

    public function cancel(Outing $outing): void
    {
        $outing->setStatus(Outing::ETAT_ANNULEE); 
    }

    public function delete(Outing $outing): void
    {
        $this->em->remove($outing);
    }

    public function autoRegisterOrganizer(Outing $outing, User $organizer): void
    {
        $registration = new Registration();
        $registration->setOuting($outing);
        $registration->setParticipant($organizer);
        $registration->setRegistrationDate(new DateTimeImmutable());

        $this->em->persist($registration);
    }

    public function save(object|array $objects): void
    {
        if (!is_array($objects))
        {
            $objects = [$objects];
        }

        foreach ($objects as $object)
        {
            $this->em->persist($object);
        }

        $this->em->flush();
    }
}