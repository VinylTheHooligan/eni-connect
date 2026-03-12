<?php

namespace App\Security\Voter;

use App\Entity\Outing;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class OutingVoter extends Voter
{
    public const REGISTER = 'OUTING_REGISTER';
    public const UNREGISTER = 'OUTING_UNREGISTER';
    public const VIEW = 'OUTING_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::REGISTER, self::UNREGISTER, self::VIEW])
            && $subject instanceof Outing;
    }

    protected function voteOnAttribute(string $attribute, mixed $outing, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($outing, $user),
            self::REGISTER => $this->canRegister($outing, $user),
            self::UNREGISTER => $this->canUnregister($outing, $user),
            default => false,
        };
    }

    private function canView(Outing $outing, User $user): bool
    {
        return true;
    }

    private function canRegister(Outing $outing, User $user): bool
    {

        if ($outing->isPast()) return false;
        if ($outing->isMaxRegistrationsReached()) return false;
        if ($outing->getRegistrationFor($user) !== null) return false;

        return true;
    }

    private function canUnregister(Outing $outing, User $user): bool
    {
        if ($outing->isPast()) return false;
        return ($outing->getRegistrationFor($user) !== null);
    }
}
