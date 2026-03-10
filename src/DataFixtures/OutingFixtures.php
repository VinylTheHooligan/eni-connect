<?php

namespace App\DataFixtures;

use App\Entity\Outing;
use App\Entity\Place;
use App\Entity\User;
use App\Services\FixturesDataProvider;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

class OutingFixtures extends Fixture implements DependentFixtureInterface
{
    private array $placeAssigned = [];

    public function __construct(
        private FixturesDataProvider $provider,
    )
    {}

    public function load(ObjectManager $om): void
    {
        $faker = $this->provider->faker();

        for ($i = 1; $i <= $this->provider->getOrganizerCount(); $i++)
        {
            $outing = new Outing();

            $outing->setName($faker->sentence(rand(3, 5), true));

            $datesTimes = $this->generateDatesTimes($faker);

            $outing->setCreatedDateTime(
                \DateTimeImmutable::createFromMutable($datesTimes['dateCreated'])
            );
            $outing->setStartDateTime(
                \DateTimeImmutable::createFromMutable($datesTimes['dateStart'])
            );
            $outing->setRegistrationDeadline(
                \DateTimeImmutable::createFromMutable($datesTimes['dateLimit'])
            );

            $outing->setDuration(rand(60, 480));
            $outing->setMaxRegistrations(rand(5, 15));

            $outing->setEventInfo($faker->sentence(rand(15,60), true));

            $organizer = $this->getReference('organizer' . $i, User::class);
            
            $outing->setOrganizer($organizer);
            

            $outing->setCampus($organizer->getCampus());
            $this->setPlaceByCampus($organizer, $outing);

            $outing->setPublished(true);

            $om->persist($outing);

            $this->addReference('outing' . $i, $outing);
        }

        $om->flush();
    }

    private function setPlaceByCampus(User $organizer, Outing &$outing): void
    {
        $campus = $organizer->getCampus();

        $campusPlaces = [];

        for ($i = 1; $i <= $this->provider->getPlaceCount(); $i++)
        {
            /** @var Place $place */
            $place = $this->getReference('place' . $i, Place::class);

            if ($place->getCampus() === $campus)
            {
                $campusPlaces['place' . $i] = $place;
            }
        }

        if (empty($campusPlaces)) 
        {
            throw new \RuntimeException("Aucune place trouvée pour le campus " . $campus->getName());
        }

        $available = array_diff_key($campusPlaces, array_flip($this->placeAssigned));

        if (empty($available))
        {
            throw new \RuntimeException("Toutes les places du campus " . $campus->getName() . " ont déjà été assignées.");
        }

        $ref = array_rand($available);
        $place = $available[$ref];

        $this->placeAssigned[] = $ref;

        $outing->setPlace($place);
    }

    private function generateDatesTimes(Generator $faker): array
    {
        $dateCreated = $faker->dateTimeBetween('-1 year', '-1 week', 'Europe/Paris');
        $dateLimit = $faker->dateTimeBetween($dateCreated, '+2 months', 'Europe/Paris');
        $dateStart = $faker->dateTimeBetween($dateLimit, '+3 months', 'Europe/Paris');

        return [
            'dateCreated' => $dateCreated,
            'dateStart' => $dateStart,
            'dateLimit' => $dateLimit,
        ];
    }

    public function getDependencies(): array
    {
        return [
            MyAccountFixtures::class,
            UserFixtures::class,
            CampusFixtures::class,
            PlaceFixtures::class,
        ];
    }
}

