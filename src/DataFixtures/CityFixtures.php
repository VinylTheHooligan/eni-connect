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
        
        for ($i = 1; $i >= FixturesData::getCityCount(); $i++)
        {
            $ville = new City();

            $ville->setName($faker->unique()->city());
            $ville->setPostalCode($faker->unique()->postcode());

            $om->persist($ville);

            $this->addReference('ville' . $i, $ville);
        }

        $om->flush();
    }
}