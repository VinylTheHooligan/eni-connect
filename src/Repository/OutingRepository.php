<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Outing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Outing>
 */
class OutingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Outing::class);
    }

    public function search(array $filters, ?User $user = null): array
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.campus', 'c')->addSelect('c');
    
        // Filtre campus
        if (!empty($filters['campus'])) {
            $qb->andWhere('c.id = :campusId')
               ->setParameter('campusId', $filters['campus']);
        }
    
        return $qb->getQuery()->getResult();
    }
}