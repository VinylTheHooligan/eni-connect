<?php

namespace App\Repository;

use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<City>
 */
class CityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    public function findBySearch(string $q = '', string $sort = 'name', string $order = 'asc'): array
    {
        $allowed = ['name', 'postalCode'];
        if (!in_array($sort, $allowed)) $sort = 'name';
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $qb = $this->createQueryBuilder('c');
        if ($q) {
            $qb->where('c.name LIKE :q')
                ->orWhere('c.postalCode LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        return $qb->orderBy('c.' . $sort, $order)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return City[] Returns an array of City objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }
    //
    //    public function findOneBySomeField($value): ?City
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

