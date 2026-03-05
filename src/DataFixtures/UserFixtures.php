<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\User;
use App\Services\FixturesDataProvider as FixturesData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Generator;

class UserFixtures extends Fixture implements DependentFixtureInterface
{

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    )
    {}

    public function load(ObjectManager $om): void
    {
        $faker = FixturesData::faker();
        
        $this->adminCreation($faker, $om);
        $this->organizerCreation($faker, $om);
        $this->userCreation(FixturesData::getUserCount(), $faker, $om);

        $om->flush();
    }

    private function userCreation(int $count, Generator $faker, ObjectManager $om): void
    {
        for ($i = 1; $i <= $count; $i++)
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

            $user->setPhoneNumber(
                $faker->randomElement(['06', '07']) . $faker->numerify('########')
            );

            $user->setCampus($this->getReference('campus' . rand(1, FixturesData::getCampusCount()), Campus::class));

            $om->persist($user);

            $this->addReference('user' . $i, $user);
        }
    }

    private function organizerCreation(Generator $faker, ObjectManager $om)
    {
        for ($i = 1; $i <= FixturesData::getOrganizerCount(); $i++)
        {
            $organizer = new User();

            $organizer->setFirstName($faker->firstName());
            $organizer->setLastName($faker->lastName());
            $organizer->setUsername('manager' . $i);

            $organizer->setEmail('organizer' . $i . '@eni.fr');
            $organizer->setRoles(['ROLE_ORGANIZER']);

            $organizer->setPasswordHash(
                $this->hasher->hashPassword($organizer, '123456789')
            );

            $organizer->setPhoneNumber(
                $faker->randomElement(['06', '07']) . $faker->numerify('########')
            );

            $organizer->setCampus($this->getReference('campus' . $i, Campus::class));

            $om->persist($organizer);

            $this->addReference('organizer' . $i, $organizer);
        }
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

        $admin->setPhoneNumber(
            $faker->randomElement(['06', '07']) . $faker->numerify('########')
        );
        $admin->setCampus(null);

        $om->persist($admin);
    }

    public function getDependencies(): array
    {
        return [
            CampusFixtures::class,
        ];
    }
}
