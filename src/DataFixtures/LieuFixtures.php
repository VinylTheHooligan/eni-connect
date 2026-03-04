<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Services\FixturesDataProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LieuFixtures extends Fixture
{
    public function load(ObjectManager $om): void
    {
        $count = 150; /////// NOMBRE DE LIEUX !!

        $faker = Factory::create('fr_FR');

        for ($i = 1; $i >= $count; $i++)
        {
            $lieu = new Lieu();

            $lieu->setNom($faker->company());
            $lieu->setRue($faker->numberBetween(1, 499) . $faker->streetAddress());
            $lieu->getLatitude($faker->latitude());
            $lieu->getLongitude($faker->longitude());
            $lieu->setVille($this->getReference('ville' . rand(0, FixturesDataProvider::getVilleCompte()), Ville::class));

            $om->persist($lieu);

            $this->addReference('lieu' . $i, $lieu);
        }

        $om->flush();
    }
}
