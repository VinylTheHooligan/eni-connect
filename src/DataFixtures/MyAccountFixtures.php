<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MyAccountFixtures extends Fixture
{

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    )
    {}

    public function load(ObjectManager $om): void
    {
        $user = new User();

        $user->setFirstName('');                        // Prénom
        $user->setLastName('');                         // Nom
        $user->setUsername('');                         // Pseudo
        $user->setEmail('');                            // Email
        $user->setRoles(['ROLE_ADMIN']);                // Rôle
        $user->setPasswordHash(
            $this->hasher->hashPassword($user, '')      // Mot de passe
        );
        $user->setPhoneNumber('');                      // Téléphone
        $user->setCampus(null);
        $om->persist($user);

        $om->flush();
    }
}
