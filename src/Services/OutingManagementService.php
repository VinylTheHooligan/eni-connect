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

    public function initializeOuting(User $user, bool $isAdmin): Outing
    {
        $outing = new Outing();

        if (!$isAdmin)
        {
            $outing->setOrganizer($user);
            $outing->setCampus($user->getCampus());
        } else
        {
            
        }

        return $outing;
    }

    public function publish(Outing $outing): void
    {
        if ($outing->getStatus() !== Outing::ETAT_CREATION)
        {
            throw new LogicException("Impossible de publier une sortie qui n'est pas en création");
        }

        $outing->setPublished();
    }

    public function cancel(Outing $outing): void
    {
        if ($outing->getStatus() === Outing::ETAT_ANNULEE)
        {
            throw new LogicException("Impossible d'annuler une sortie qui déjà annulée");
        }

        $outing->setCancelled();
    }

    public function delete(Outing $outing): void
    {
        $this->em->remove($outing);
    }

    public function autoRegisterOrganizer(Outing $outing, User $organizer): Registration
    {
        $registration = new Registration();
        $registration->setOuting($outing);
        $registration->setParticipant($organizer);
        $registration->setRegistrationDate(new DateTimeImmutable());
    
        return $registration;
    }


    public function save(object|array $objects): void
    {
        if (!is_array($objects))
        {
            $objects = [$objects];
        }

        foreach ($objects as $object)
        {
            if ($object === null)
            {
                continue;
            }

            $this->em->persist($object);
        }

        $this->em->flush();
    }
}