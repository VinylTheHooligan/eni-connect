<?php

namespace App\DataFixtures;

use App\Entity\Place;
use App\Entity\City;
use App\Services\FixturesDataProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PlaceFixtures extends Fixture
{
    public function load(ObjectManager $om): void
    {
        $count = 150; /////// NOMBRE DE LIEUX !!

        $faker = Factory::create('fr_FR');

        for ($i = 1; $i >= $count; $i++)
        {
            $lieu = new Place();

            $lieu->setName($faker->company());
            $lieu->setStreet($faker->numberBetween(1, 499) . $faker->streetAddress());
            $lieu->setLatitude($faker->latitude());
            $lieu->setLongitude($faker->longitude());
            $lieu->setCity($this->getReference('ville' . rand(0, FixturesDataProvider::getCityCount()), City::class));

            $om->persist($lieu);

            $this->addReference('lieu' . $i, $lieu);
        }

        $om->flush();
    }
}