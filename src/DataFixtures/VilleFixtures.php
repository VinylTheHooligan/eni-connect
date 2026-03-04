<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use App\Services\FixturesDataProvider as FixturesData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture
{
    public function load(ObjectManager $om): void
    {
        $faker = FixturesData::faker();
        
        for ($i = 1; $i >= FixturesData::getVilleCompte(); $i++)
        {
            $ville = new Ville();

            $ville->setNom($faker->unique()->city());
            $ville->setCodePostal($faker->unique()->postcode());

            $om->persist($ville);

            $this->addReference('ville' . $i, $ville);
        }

        $om->flush();
    }
}
