<?php

namespace App\Controller;

use App\Entity\Outing;
use App\Form\OutingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/sorties/gestion', name: 'gestion_')]
#[IsGranted('ROLE_ORGANIZER')]
final class OutingManagerController extends AbstractController
{
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

    // modification d'une sortie les zobs !! :)
    #[Route('/{id}/modifier', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Outing $outing, Request $request, EntityManagerInterface $em): Response
    {
        // seulemtn l'organisateur peut modifier la sortie
        if ($outing->getOrganizer() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette sortie.');
        }

        $form = $this->createForm(OutingType::class, $outing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $action = $request->request->get('action');

            if ($action === 'delete') {
                $em->remove($outing);
                $em->flush();
                $this->addFlash('success', 'La sortie a bien été supprimée.');
                return $this->redirectToRoute('outing_list');
            }

            if ($action === 'publish') {
                $outing->setStatus(Outing::ETAT_OUVERTE);
                $this->addFlash('success', 'La sortie a été publiée !');
            } else {
                $this->addFlash('success', 'La sortie a bien été modifiée.');
            }

            $em->flush();
            return $this->redirectToRoute('outing_detail', ['id' => $outing->getId()]);
        }

        return $this->render('outing/edit.html.twig', [
            'outingForm' => $form->createView(),
            'outing' => $outing,
        ]);
    }
}
