<?php

namespace App\Services;

use App\Entity\Outing;
use App\Entity\Registration;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class OutingControllerService
{
    public function __construct(
        private EntityManagerInterface $em,
    )
    {}

    public function register(Outing $outing, User $user): array
    {
        if ($outing->getOrganizer() === $user)
        {
            return ['warning', 'Vous ne pouvez pas vous inscrire à votre propre sortie.'];
        }

        if (!$outing->isOpen())
        {
            return ['warning', 'Vous ne pouvez vous inscrire que sur une sortie ouverte.'];
        }

        if ($outing->isRegistrationDeadlinePassed())
        {
            return ['warning', "La date limite d'inscription est dépassée."];
        }

        if ($outing->isMaxRegistrationsReached())
        {
            return ['warning', 'Le nombre maximal de participants est atteint.'];
        }

        if ($outing->isRegistered($user))
        {
            return ['warning', 'Vous êtes déjà inscrit à cette sortie.'];
        }

        $registration = new Registration();
        $registration->setParticipant($user);
        $registration->setOuting($outing);

        $this->em->persist($registration);
        $this->em->flush();

        return ['success', 'Vous êtes inscrit à la sortie.'];
    }

    public function unregister(Outing $outing, User $user): array
    {
        if ($outing->getOrganizer() === $user)
        {
            return ['warning', "Vous ne pouvez pas vous désister de votre propre sortie."];
        }

        if ($outing->isStarted())
        {
            return ['warning', 'Vous ne pouvez plus vous désister car la sortie a débuté.'];
        }

        $registration = $outing->getRegistrationFor($user);

        if (!$registration)
        {
            return ['info', 'Vous n’êtes pas inscrit.'];
        }

        $this->em->remove($registration);
        $this->em->flush();

        return ['success', 'Vous vous êtes désisté de la sortie.'];
    }
}