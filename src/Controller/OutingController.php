<?php

namespace App\Controller;

use App\Entity\Outing;
use App\Entity\User;
use App\Repository\OutingRepository;
use App\Repository\CampusRepository;
use App\Services\OutingControllerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/sorties', name: 'outing_')]
class OutingController extends AbstractController
{
    public function __construct(
        private readonly OutingRepository $outingRepository,
        private readonly CampusRepository $campusRepository,
        private readonly OutingControllerService $ocs,
    )
    {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $campuses = $this->campusRepository->findAll();

        // Campus sélectionné dans les filtres ou, par défaut, campus de l'utilisateur connecté
        $campusId = $request->query->get('campus');

        $user = $this->getUser();
        assert($user instanceof User);

        if ($campusId === null && $user?->getCampus())
        {
            $campusId = $user->getCampus()->getId();
        }

        $filters = [
            'campus' => $campusId,
            'q' => $request->query->get('q'),
            'dateFrom' => $request->query->get('dateFrom'),
            'dateTo' => $request->query->get('dateTo'),
            'isOrganizer' => $request->query->getBoolean('isOrganizer'),
            'isRegistered' => $request->query->getBoolean('isRegistered'),
            'isNotRegistered' => $request->query->getBoolean('isNotRegistered'),
            'isPast' => $request->query->getBoolean('isPast'),
        ];

        $outings = $this->outingRepository->search($filters, $user);

        return $this->render('outing/index.html.twig', [
            'outings' => $outings,
            'campuses' => $campuses,
            'filters' => $filters,
        ]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(Outing $outing): Response
    {
        return $this->render('outing/detail.html.twig', [
            'outing' => $outing,
        ]);
    }

    #[Route('/{id}/inscription', name: 'register', requirements: ['id' => '\d+'], methods: ['POST','GET'])]
    public function register(Outing $outing): Response
    {
        $this->denyAccessUnlessGranted('OUTING_REGISTER', $outing);

        $user = $this->getUser();
        $response = $this->ocs->register($outing, $user);

        $this->addFlash($response[0], $response[1]);
        return $this->redirectToList();
    }

    #[Route('/{id}/desistement', name: 'unregister', requirements: ['id' => '\d+'], methods: ['POST','GET'])]
    public function unregister(Outing $outing): Response
    {
        $this->denyAccessUnlessGranted('OUTING_UNREGISTER', $outing);

        $user = $this->getUser();
        $response = $this->ocs->unregister($outing, $user);

        $this->addFlash($response[0], $response[1]);
        return $this->redirectToList();
    }

    // Méthode privé
    private function redirectToList(): Response
    {
        return $this->redirectToRoute('outing_list');
    }
}
