<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Outing;
use App\Entity\Place;
use App\Entity\User;
use App\Services\FixturesDataProvider as FixturesData;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

class OutingFixtures extends Fixture implements DependentFixtureInterface
{
    private array $campusAssigned = [];
    private array $placeAssigned = [];

    public function load(ObjectManager $om): void
    {
        $faker = FixturesData::faker();

        for ($i = 1; $i <= FixturesData::getOrganizerCount(); $i++)
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

            $outing->setStatus($this->statusCheck($datesTimes, $outing));

            $outing->setOrganizer($this->getReference('organizer' . $i, User::class));

            $outing->setCampus($this->getReference(
                $this->checkAssigned('campus', FixturesData::getCampusCount(), $this->campusAssigned), Campus::class)
            );
            $outing->setPlace($this->getReference(
                $this->checkAssigned('place', FixturesData::getPlaceCount(), $this->placeAssigned), Place::class)
            );

            $outing->setPublished(true);

            $om->persist($outing);

            $this->addReference('outing' . $i, $outing);
        }

        $om->flush();
    }

    private function statusCheck(array $datesTimes, Outing $outing): string
    {
        $now = new \DateTimeImmutable('now');
        $start = $datesTimes['dateStart'];
        $limit = $datesTimes['dateLimit'];
        $timeOutingEnd = (clone $start)->modify('+' . $outing->getDuration() . ' minutes');

        if ($now < $start)
        {
            return Outing::ETAT_OUVERTE;
        }

        if ($now >= $limit && $now < $start)
        {
            return Outing::ETAT_CLOTUREE;
        }

        if ($now >= $start && $now < $timeOutingEnd)
        {
            return Outing::ETAT_EN_COURS;
        }

        return Outing::ETAT_TERMINEE;
    }

    // Entité : nom abstrait, désigne soit les lieux, soit les campus
    private function checkAssigned(string $entityName, int $entitiesCount, array &$assignedArray): string
    {
        $allEntities = [];

        for ($i = 1; $i <= $entitiesCount; $i++)
        {
            $allEntities[] = $entityName . $i;
        }

        $available = array_diff($allEntities, $assignedArray);

        if (empty($available))
        {
            throw new \RuntimeException('Tous les entités ont déjà été assignés.');
        }

        $toAssigned = $available[array_rand($available)];

        $assignedArray[] = $toAssigned;

        return $toAssigned;
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

