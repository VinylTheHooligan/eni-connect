<?php

namespace App\Controller\Api;

use App\Repository\OutingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
#[IsGranted('ROLE_USER')]
final class APIOutingController extends AbstractController
{
    #[Route('/sorties', name: 'api_sorties', methods: ['GET'])]
    public function sorties(Request $request, OutingRepository $outingRepository): JsonResponse
    {
        $sorties = $outingRepository->findForApi(
            $request->query->get('etat'),
            $request->query->get('date')
        );

        $data = array_map(fn($outing) => [
            'id' => $outing->getId(),
            'nom' => $outing->getName(),
            'dateDebut' => $outing->getStartDateTime()?->format('Y-m-d H:i'),
            'duree' => $outing->getDuration(),
            'etat' => $outing->getStatus(),
            'campus' => $outing->getCampus()?->getName(),
            'organisateur' => $outing->getOrganizer()?->getUsername(),
            'nbInscrits' => $outing->getRegistrations()->count(),
            'nbPlaces' => $outing->getMaxRegistrations(),
        ], $sorties);

        return $this->json($data);
    }
}
