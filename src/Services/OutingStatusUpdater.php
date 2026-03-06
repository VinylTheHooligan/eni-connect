<?php

/// Service à remplacer par CRON ou event subscriber si beaucoup trop d'outings actif

namespace App\Services;

use App\Entity\Outing;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class OutingStatusUpdater {

    private const TIME_BEFORE_ARCHIVED = '+7 days';

    public function __construct(private EntityManagerInterface $em)
    {}

    public function updateStatuses(array $outings): array
    {
        $now = new DateTimeImmutable();
        $updated = 0;

        foreach ($outings as $outing)
        {
            /** @var Outing $outing */

            if ($outing->getStatus() === Outing::ETAT_CREATION) continue;
            if ($outing->getStatus() === Outing::ETAT_HISTORISEE) continue;

            $limit = $outing->getRegistrationDeadline();
            $start = $outing->getStartDateTime();
            $end = $start->modify('+' . $outing->getDuration() . ' minutes');
            $archived = (clone $end)->modify(self::TIME_BEFORE_ARCHIVED);

            $newStatus = $this->computeStatus($now, $limit, $start, $end, $archived);

            // si le status change, alors on set définitivement le status, on persiste et on incrémente
            if ($outing->getStatus() !== $newStatus)
            {
                $outing->setStatus($newStatus);
                $this->em->persist($outing);
                $updated++;
            }
        }

        if ($updated > 0) $this->em->flush();

        return $outings;
    }

    private function computeStatus(
        DateTimeImmutable $now,
        DateTimeImmutable $limit,
        DateTimeImmutable $start,
        DateTimeImmutable $end,
        DateTimeImmutable $archived
    ): string
    {

        if ($now >= $archived) return Outing::ETAT_HISTORISEE;
        if ($now >= $end) return Outing::ETAT_TERMINEE;
        if ($now >= $start) return Outing::ETAT_EN_COURS;
        if ($now >= $limit) return Outing::ETAT_CLOTUREE;

        return Outing::ETAT_OUVERTE;
    }

}