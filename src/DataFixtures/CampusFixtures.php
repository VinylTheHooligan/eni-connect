<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Services\FixturesDataProvider as FixturesData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $om): void
    {
        $faker = FixturesData::faker();

        for ($i = 1; $i <= FixturesData::getCampusCount(); $i++)
        {
            $campus = new Campus();

            $campus->setName('Campus de ' . $faker->city());

            $om->persist($campus);

            $this->addReference('campus' . $i, $campus);
        }

        $om->flush();
    }
}
