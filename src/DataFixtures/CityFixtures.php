<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Services\FixturesDataProvider as FixturesData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CityFixtures extends Fixture
{
    public function load(ObjectManager $om): void
    {
        $faker = FixturesData::faker();
        
        for ($i = 1; $i <= FixturesData::getCityCount(); $i++)
        {
            $town = new City();

            $town->setName($faker->unique()->city());
            $town->setPostalCode($faker->unique()->postcode());

            $om->persist($town);

            $this->addReference('ville' . $i, $town);
        }

        $om->flush();
    }
}