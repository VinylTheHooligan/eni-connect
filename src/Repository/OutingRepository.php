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
            ->leftJoin('o.campus', 'c')->addSelect('c')
            ->leftJoin('o.registrations', 'r')->addSelect('r')
            ->leftJoin('r.participant', 'p');

        // 1) Statut : sorties publiées visibles pour tous,
        //    sorties "en création" uniquement visibles par leur organisateur
        if ($user) {
            $qb
                ->andWhere('o.status != :statusCreation OR (o.status = :statusCreation AND o.organizer = :currentUser)')
                ->setParameter('statusCreation', Outing::ETAT_CREATION)
                ->setParameter('currentUser', $user);
        } else {
            $qb
                ->andWhere('o.status != :statusCreation')
                ->setParameter('statusCreation', Outing::ETAT_CREATION);
        }

        // 2) Filtre campus
        if (!empty($filters['campus'])) {
            $qb->andWhere('c.id = :campusId')
               ->setParameter('campusId', $filters['campus']);
        }

        // 3) Filtre texte sur le nom
        if (!empty($filters['q'])) {
            $qb->andWhere('LOWER(o.name) LIKE :search')
               ->setParameter('search', '%'.mb_strtolower($filters['q']).'%');
        }

        // 4) Filtre sur la plage de dates de la sortie
        if (!empty($filters['dateFrom'])) {
            $qb->andWhere('o.startDateTime >= :dateFrom')
               ->setParameter('dateFrom', new \DateTimeImmutable($filters['dateFrom']));
        }
        if (!empty($filters['dateTo'])) {
            // On inclut toute la journée de fin
            $dateTo = new \DateTimeImmutable($filters['dateTo'] . ' 23:59:59');
            $qb->andWhere('o.startDateTime <= :dateTo')
               ->setParameter('dateTo', $dateTo);
        }

        // 5) Filtres liés au participant courant
        if ($user) {
            if (!empty($filters['isOrganizer'])) {
                $qb->andWhere('o.organizer = :userOrganizer')
                   ->setParameter('userOrganizer', $user);
            }

            if (!empty($filters['isRegistered'])) {
                $qb->andWhere('p = :userRegistered')
                   ->setParameter('userRegistered', $user);
            }

            if (!empty($filters['isNotRegistered'])) {
                $qb->andWhere('p IS NULL OR p != :userNotRegistered')
                   ->setParameter('userNotRegistered', $user);
            }
        }

        // 6) Sorties passées
        if (!empty($filters['isPast'])) {
            $qb->andWhere('o.startDateTime < :nowPast')
               ->setParameter('nowPast', new \DateTimeImmutable());
        }

        return $qb->getQuery()->getResult();
    }
}