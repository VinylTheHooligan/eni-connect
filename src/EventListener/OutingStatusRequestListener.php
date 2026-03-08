<?php

namespace App\EventListener;

use App\Repository\OutingRepository;
use App\Services\OutingStatusUpdater;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class OutingStatusRequestListener
{
    private bool $updated = false;

    public function __construct(
        private OutingRepository $outingRepository,
        private OutingStatusUpdater $outingStatusUpdater,
    )
    {}

    public function onRequestEvent(RequestEvent $event): void
    {
        if (!$event->isMainRequest())
        {
            return;
        }

        if ($this->updated)
        {
            return;
        }

        $outings = $this->outingRepository->findAll();
        $this->outingStatusUpdater->updateStatuses($outings);

        $this->updated = true;
    }
}
