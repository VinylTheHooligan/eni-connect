<?php

namespace App\Services\Admin;

use App\Entity\City;
use App\Repository\CityRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class CityManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private CityRepository $cityRepository,
    )
    {}

    /**
     * @return City[]
     */
    public function search(?string $query, string $sort = 'name', string $order = 'asc'): array
    {
        return $this->cityRepository->findBySearch($query, $sort, $order);
    }

    public function save(City $city): city
    {
        $this->em->persist($city);
        $this->em->flush();
        return $city;
    }

    public function canBeDeleted(City $city): bool
    {
        return $city->getPlaces()->isEmpty();
    }

    public function remove(City $city)
    {
        if (!$this->canBeDeleted($city))
        {
            return false;
        }

        try {
            $this->em->remove($city);
            $this->em->flush();
            return true;
        } catch (ForeignKeyConstraintViolationException)
        {
            return false;
        }
    }
}