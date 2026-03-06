<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findBySearch(string $q = '', string $sort = 'lastName', string $order = 'asc'): array
    {
        $allowed = ['username', 'lastName', 'firstName'];
        if (!in_array($sort, $allowed)) $sort = 'lastName';
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $qb = $this->createQueryBuilder('u');
        if ($q) {
            $qb->where('u.username LIKE :q')
                ->orWhere('u.lastName LIKE :q')
                ->orWhere('u.firstName LIKE :q')
                ->orWhere('u.email LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        return $qb->orderBy('u.' . $sort, $order)
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
