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
        
        $this->userCreation(FixturesData::getUserCount(), $faker, $om);
        $this->adminCreation($faker, $om);

        $om->flush();
    }

    private function userCreation(int $count, Generator $faker, ObjectManager $om): void
    {
        for ($i = 0; $i < $count; $i++)
        {
            $user = new User();

            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setUsername('user' . $i);

            $user->setEmail('user' . $i . '@eni.fr');
            $user->setRoles(['ROLE_USER']);

            $user->setPasswordHash(
                $this->hasher->hashPassword($user, '123456789')
            );

            $user->setPhoneNumber($faker->phoneNumber());
        }

        $om->persist($user);
    }

    private function adminCreation(Generator $faker, ObjectManager $om): void
    {
        $admin = new User();

        $admin->setFirstName($faker->firstName());
        $admin->setLastName($faker->lastName());
        $admin->setUsername('admin');

        $admin->setEmail('admin@eni.fr');
        $admin->setRoles(['ROLE_ADMIN']);

        $admin->setPasswordHash(
            $this->hasher->hashPassword($admin, '123456789')
        );

        $admin->setPhoneNumber($faker->phoneNumber());

        $om->persist($admin);
    }
}
