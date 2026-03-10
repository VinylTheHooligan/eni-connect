<?php

namespace App\Security\Voter;

use App\Entity\Outing;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class OutingManagerVoter extends Voter
{
    public const CREATE = 'CREATE';
    public const EDIT = 'EDIT';
    public const PUBLISH = 'PUBLISH';
    public const CANCEL = 'CANCEL';
    public const DELETE = 'DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute === self::CREATE)
        {
            return true;
        }

        return in_array($attribute, [
            self::EDIT,
            self::PUBLISH,
            self::CANCEL,
            self::DELETE,
        ], true) && $subject instanceof Outing;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        // si l'user est anonyme, pas accès
        if (!$user instanceof User)
        {
            return false;
        }

        // Organisateur & admin peuvent créer des sorties
        if ($attribute === self::CREATE)
        {
            return in_array('ROLE_ORGANIZER', $user->getRoles(), true);
        }

        /** @var Outing $outing */
        $outing = $subject;

        $isOwner = $outing->getOrganizer() === $user;
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles(), true);

        // L'admin ne peut que cancel l'outing à partir de là
        if ($isAdmin && $attribute !== self::CANCEL)
        {
            return false;
        }

        if (!$isOwner && !$isAdmin)
        {
            return false;
        }

        return match ($attribute)
        {
            // Edit si la sortie est en création ou ouverte
            self::EDIT => $outing->getStatus() === Outing::ETAT_CREATION
                || $outing->getStatus() === Outing::ETAT_OUVERTE,

            // Publish seulement si en création ET date limite non dépassée
            self::PUBLISH =>
                $outing->getStatus() === Outing::ETAT_CREATION
                && !$outing->isRegistrationDeadlinePassed(),

            // cancel seulement si non commencée et non déjà annulée
            self::CANCEL =>
                !$outing->isStarted()
                && $outing->getStatus() !== Outing::ETAT_ANNULEE,

            // Delete seulement si en création (jamais publiée) et non commencée
            self::DELETE =>
                $outing->getStatus() === Outing::ETAT_CREATION
                && !$outing->isStarted(),

            default => false,
        };
    }
}
