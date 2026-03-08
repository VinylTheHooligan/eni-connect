<?php

namespace App\DataFixtures;

use App\Entity\Outing;
use App\Entity\Registration;
use App\Entity\User;
use App\Services\FixturesDataProvider;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

class RegistrationFixtures extends Fixture implements DependentFixtureInterface
{
    private array $userRegistered = [];

    public function __construct(
        private FixturesDataProvider $provider,
    )
    {}

    public function load(ObjectManager $om): void
    {
        $faker = $this->provider->faker();

        // OrganizerCount est également le nombre de sorties, car 1 organizer par sortie
        for ($i = 1; $i <= $this->provider->getOrganizerCount(); $i++)
        {
            $outing = $this->getReference('outing' . $i, Outing::class);
            $organizer = $this->getReference('organizer' . $i, User::class);
            
            $registrationOrganizer = new Registration();
            $registrationOrganizer->setOuting($outing);
            $registrationOrganizer->setRegistrationDate($this->generateRegistrationDate($faker, $outing));
            $registrationOrganizer->setParticipant($organizer);
            
            $this->userRegistered[] = $organizer;

            $om->persist($registrationOrganizer);

            $nbParticipant = $outing->getMaxRegistrations() - 1;
            $this->createUserRegistrations($faker, $outing, $nbParticipant, $om);
        }

        $om->flush();
    }

    private function createUserRegistrations(Generator $faker, Outing $outing, int $nbParticipant, ObjectManager $om)
    {
        $userCount = $this->provider->getUserCount();

        $alreayRegistered = [];

        for ($i = 1; $i <= rand(0, $nbParticipant); $i++)
        {   
            $user = null;

            do
            {
                $userId = rand(1, $userCount);
            } while (in_array($userId, $alreayRegistered));

            $user = $this->getReference('user' . rand(1, $userCount), User::class);

            $alreayRegistered[] = $userId;
                        
            $registration = new Registration();

            $registration->setOuting($outing);
            $registration->setParticipant($user);
            $registration->setRegistrationDate($this->generateRegistrationDate($faker, $outing));

            $user->setRoles(['ROLE_PARTICIPANT']);

            $om->persist($registration);

            // si le nombre maximal d'inscription est atteint
            if ($outing->getRegistrations()->count() >= $outing->getMaxRegistrations())
            {
                $outing->setStatus(Outing::ETAT_CLOTUREE);
            }
        }
    }

    private function generateRegistrationDate(Generator $faker, Outing $outing): DateTimeImmutable
    {
        $dateCreated = \DateTime::createFromImmutable($outing->getCreatedDateTime());
        $dateLimit = \DateTime::createFromImmutable($outing->getRegistrationDeadline());

        $randomDate = $faker->dateTimeBetween($dateCreated, $dateLimit);

        return DateTimeImmutable::createFromMutable($randomDate);
    }


    public function getDependencies(): array
    {
        return [
            OutingFixtures::class,
            UserFixtures::class,
        ];
    }
}