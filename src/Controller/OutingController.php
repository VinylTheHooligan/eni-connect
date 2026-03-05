<?php

namespace App\Controller;

use App\Repository\OutingRepository;
use App\Repository\CampusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/sorties', name: 'outing_')]
#[IsGranted('ROLE_USER')]
class OutingController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(OutingRepository $outingRepository, CampusRepository $campusRepository, Request $request): Response
    {
        $campuses = $campusRepository->findAll();
        $campusId = $request->query->get('campus');
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


        $outings = $outingRepository->search($filters, $this->getUser());

        return $this->render('outing/index.html.twig', [
            'outings' => $outings,
            'campuses' => $campuses,
            'filters' => $filters,

        ]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail($id, OutingRepository $outingRepository): Response
    {
        $outing = $outingRepository->find($id);

        return $this->render('outing/detail.html.twig', [
            'outing' => $outing,
        ]);
    }
}
