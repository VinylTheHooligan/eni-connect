<?php

namespace App\DataFixtures;

use App\Entity\Place;
use App\Entity\City;
use App\Services\FixturesDataProvider as FixturesData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PlaceFixtures extends Fixture
{
    public function load(ObjectManager $om): void
    {
        $faker = FixturesData::faker();

        for ($i = 1; $i <= FixturesData::getPlaceCount(); $i++)
        {
            $place = new Place();

            $place->setName($faker->company());
            $place->setStreet($faker->numberBetween(1, 499) . $faker->streetAddress());
            $place->setLatitude($faker->latitude());
            $place->setLongitude($faker->longitude());
            $place->setCity($this->getReference('ville' . rand(1, FixturesData::getCityCount()), City::class));

            $om->persist($place);

            $this->addReference('place' . $i, $place);
        }

        $om->flush();
    }
}