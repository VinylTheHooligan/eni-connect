<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Services\FixturesDataProvider as FixturesData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Generator;

class UserFixtures extends Fixture
{

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    )
    {}

    public function load(ObjectManager $om): void
    {
        $faker = FixturesData::faker();
        
        $this->userCreation(FixturesData::getUtilisateurCompte(), $faker, $om);
        $this->adminCreation($faker, $om);

        $om->flush();
    }

    private function userCreation(int $count, Generator $faker, ObjectManager $om): void
    {
        for ($i = 0; $i < $count; $i++)
        {
            $user = new User();

            $user->setPrenom($faker->firstName());
            $user->setNom($faker->lastName());
            $user->setPseudo('user' . $i);

            $user->setEmail('user' . $i . '@eni.fr');
            $user->setRoles(['ROLE_USER']);

            $user->setHashMotPasse(
                $this->hasher->hashPassword($user, '123456789')
            );

            $user->setTelephone($faker->phoneNumber());
        }

        $om->persist($user);
    }

    private function adminCreation(Generator $faker, ObjectManager $om): void
    {
        $admin = new User();

        $admin->setPrenom($faker->firstName());
        $admin->setNom($faker->lastName());
        $admin->setPseudo('admin');

        $admin->setEmail('admin@eni.fr');
        $admin->setRoles(['ROLE_ADMIN']);

        $admin->setHashMotPasse(
            $this->hasher->hashPassword($admin, '123456789')
        );

        $admin->setTelephone($faker->phoneNumber());

        $om->persist($admin);
    }
}
