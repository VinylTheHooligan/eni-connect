<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Place;
use App\Entity\City;
use App\Services\FixturesDataProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
class PlaceFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private FixturesDataProvider $provider,
    )
    {}

    public function load(ObjectManager $om): void
    {
        $faker = $this->provider->faker();

        for ($i = 1; $i <= $this->provider->getPlaceCount(); $i++)
        {
            $place = new Place();
            $campus = $this->getReference('campus' . rand(1, $this->provider->getCampusCount()), Campus::class);

            $place->setName($faker->company());
            $place->setStreet($faker->numberBetween(1, 499) . ' ' . $faker->streetAddress());
            $place->setLatitude($faker->latitude());
            $place->setLongitude($faker->longitude());
            $place->setCity($this->getReference('ville' . rand(1, $this->provider->getCityCount()), City::class));
            $place->setCampus($campus);

            $campus->addPlace($place);

            $om->persist($place);

            $this->addReference('place' . $i, $place);
        }

        $om->flush();
    }

    public function getDependencies(): array
    {
        return [
            CampusFixtures::class,
        ];
    }
}