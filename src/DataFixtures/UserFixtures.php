<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Faker\Factory;

class UserFixtures extends Fixture
{

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    )
    {}

    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR');
        
        // Utilisateur classique
        for ($i = 0; $i < 50; $i++)
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

            $manager->persist($user);
        }

        //Administrateur
        $admin = new User();

        $admin->setPrenom($faker->firstName());
        $admin->setNom($faker->lastName());
        $admin->setPseudo('admin');

        $admin->setEmail('admin@eni.fr');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setHashMotPasse(
            $this->hasher->hashPassword($admin, '123456789')
        );

        $user->setTelephone($faker->phoneNumber());

        $manager->persist($admin);

        $manager->flush();
    }
}
