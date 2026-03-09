<?php

namespace App\Services\Admin;

use App\Entity\Campus;
use App\Repository\CampusRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class CampusManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private CampusRepository $campusRepository,
    )
    {}

    /**
     * @return Campus[]
     */
    public function search(?string $query, string $sort = 'name', string $order = 'asc'): array
    {
        return $this->campusRepository->findBySearch($query, $sort, $order);
    }

    public function save(Campus $campus): Campus
    {
        $this->em->persist($campus);
        $this->em->flush();

        return $campus;
    }

    public function canBeDeleted(Campus $campus): bool
    {
        return $campus->getOutings()->isEmpty();
    }

    public function remove(Campus $campus): bool
    {
        if (!$this->canBeDeleted($campus))
        {
            return false;
        }

        try {
            $this->em->remove($campus);
            $this->em->flush();
            return true;
        } catch (ForeignKeyConstraintViolationException)
        {
            return false;
        }
    }
}