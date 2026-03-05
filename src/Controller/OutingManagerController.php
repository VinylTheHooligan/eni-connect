<?php

namespace App\Controller;

use App\Entity\Outing;
use App\Form\CancelOutingType;
use App\Form\OutingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/sorties/gestion', name: 'manage_')]
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
            $action = $request->request->get('action');

            if ($action === 'publish') {
                $outing->setStatus(Outing::ETAT_OUVERTE);
                $this->addFlash('success', 'La sortie a bien été créée et publiée.');
            } else {
                // Par défaut l’état est déjà ETAT_CREATION dans le constructeur
                $this->addFlash('success', 'La sortie a bien été créée et est en cours de création.');
            }

            $em->persist($outing);
            $em->flush();

            $this->addFlash('success', 'La sortie a bien été créée.');

            return $this->redirectToRoute('outing_detail', ['id' => $outing->getId()]);
        }

        return $this->render('outing/create.html.twig', [
            'outingForm' => $form->createView(),
        ]);
    }

    #[Route('/{id}/modifier', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Outing $outing, Request $request, EntityManagerInterface $em): Response
    {
        // seulemtn l'organisateur peut modifier la sortie
        if ($outing->getOrganizer() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette sortie.');
        }

        $form = $this->createForm(OutingType::class, $outing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
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

    #[Route('/{id}/publier', name: 'publish', requirements: ['id' => '\d+'], methods: ['POST','GET'])]
    public function publish(Outing $outing, EntityManagerInterface $em): Response
    {
        if ($outing->getOrganizer() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas publier cette sortie.');
        }

        if ($outing->getStatus() !== Outing::ETAT_CREATION) {
            $this->addFlash('warning', 'Seules les sorties en cours de création peuvent être publiées.');
            return $this->redirectToRoute('outing_list');
        }

        $outing->setStatus(Outing::ETAT_OUVERTE);
        $em->flush();

        $this->addFlash('success', 'La sortie a été publiée.');
        return $this->redirectToRoute('outing_list');
    }

    #[Route('/{id}/annuler', name: 'cancel', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function cancel(Outing $outing, Request $request, EntityManagerInterface $em): Response
    {

        if ($outing->getOrganizer() !== $this->getUser())
        {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette sortie.');
        }

        $form = $this->createForm(CancelOutingType::class, $outing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {

            $outing->setStatus(Outing::ETAT_ANNULEE);
            $this->addFlash('success', 'La sortie a bien été annulé !');

            $em->flush();
            return $this->redirectToRoute('outing_list');
        }

        return $this->render('outing/cancel.html.twig', [
            'outingForm' => $form->createView(),
            'outing' => $outing,
        ]);
    }
}
