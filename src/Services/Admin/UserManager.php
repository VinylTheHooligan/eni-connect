<?php

namespace App\Services\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $hasher,
    )
    {}

    /**
     * @return User[]
     */
    public function search(?string $query, string $sort = 'lastname', string $order = 'asc'): array
    {
        return $this->userRepository->findBySearch($query, $sort, $order);
    }

    public function save(User $user, string $role): User
    {
        if ($user->getPlainPassword())
        {
            $user->setPasswordHash(
                $this->hasher->hashPassword($user, $user->getPlainPassword())
            );
            $user->setPlainPassword(null);
        }

        $user->setRoles([$role]);

        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    public function toggle(User $user): void
    {
        $user->setIsActive(!$user->isActive());
        $this->em->flush();
    }
}