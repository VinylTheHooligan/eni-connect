<?php

namespace App\EventListener;

use App\Repository\OutingRepository;
use App\Services\OutingStatusUpdater;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class OutingStatusRequestListener
{
    private const CACHE_KEY = 'outing_status_last_update';
    private const UPDATE_INTERVAL = 600; // 10 minutes en secondes

    public function __construct(
        private OutingRepository $outingRepository,
        private OutingStatusUpdater $outingStatusUpdater,
        private CacheInterface $cache
    ) {}

    public function onRequestEvent(RequestEvent $event): void
    {
        if (!$event->isMainRequest())
        {
            return;
        }

        $lastUpdate = $this->cache->get(self::CACHE_KEY, function (ItemInterface $item) {
            $item->expiresAfter(self::UPDATE_INTERVAL);
            return new \DateTimeImmutable('@0'); // 1970
        });

        $now = new \DateTimeImmutable();

        if ($now->getTimestamp() - $lastUpdate->getTimestamp() > self::UPDATE_INTERVAL)
        {

            $outings = $this->outingRepository->findAll();
            $this->outingStatusUpdater->updateStatuses($outings);

            // Mise à jour de la date dans le cache
            $this->cache->delete(self::CACHE_KEY);
            $this->cache->get(self::CACHE_KEY, function (ItemInterface $item) use ($now) {
                $item->expiresAfter(self::UPDATE_INTERVAL);
                return $now;
            });
        }
    }
}
