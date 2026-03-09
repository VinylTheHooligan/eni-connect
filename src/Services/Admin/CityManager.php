<?php

namespace App\Services\Admin;

use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;

class CityManager
{
    public function __construct(
        private EntityManagerInterface $em,
    )
    {}

    public function save(City $city)
    {
        $this->em->persist($city);
        $this->em->flush();
    }

    public function remove(City $city)
    {
        $this->em->remove($city);
        $this->em->flush();
    }
}