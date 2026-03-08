<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Services\FixturesDataProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function __construct(
        private FixturesDataProvider $provider,
    )
    {}

    public function load(ObjectManager $om): void
    {
        $faker = $this->provider->faker();

        for ($i = 1; $i <= $this->provider->getCampusCount(); $i++)
        {
            $campus = new Campus();

            $campus->setName('Campus de ' . $faker->city());

            $om->persist($campus);

            $this->addReference('campus' . $i, $campus);
        }

        $om->flush();
    }
}
