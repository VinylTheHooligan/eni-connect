<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class SortieFixtures extends Fixture
{
    public function load(ObjectManager $om): void
    {
        $count = 50;

        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < $count; $i++)
        {

        }

        $om->flush();
    }
}
