<?php

namespace App\Controller;

use App\Entity\Outing;
use App\Form\OutingType;
use App\Repository\OutingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sorties', name: 'outing_')]
class OutingController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(OutingRepository $outingRepository, Request $request): Response
    {
        // TODO: récupérer les filtres depuis $request (campus, texte, cases à cocher, dates…)
        // TODO: appeler un OutingRepository::search(...) que tu écriras ensuite
        $outings = $outingRepository->findAll();

        return $this->render('outing/index.html.twig', [
            'outings' => $outings,
            // 'filters' => $filters éventuels pour réafficher le formulaire
        ]);
    }

    #[Route('/creer', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $outing = new Outing();
        $outing->setOrganizer($this->getUser());

        $form = $this->createForm(OutingType::class, $outing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Par défaut l’état est déjà ETAT_CREATION dans le constructeur
            $em->persist($outing);
            $em->flush();

            $this->addFlash('success', 'La sortie a bien été créée.');

            return $this->redirectToRoute('outing_list');
        }

        return $this->render('outing/create.html.twig', [
            'outingForm' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function detail($id, OutingRepository $outingRepository): Response
    {
        $outing = $outingRepository->find($id);

        return $this->render('outing/detail.html.twig', [
            'outing' => $outing,
        ]);
    }
}