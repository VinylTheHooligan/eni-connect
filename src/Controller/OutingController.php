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

        // Campus sélectionné dans les filtres ou, par défaut, campus de l'utilisateur connecté
        $campusId = $request->query->get('campus');

        $user = $this->getUser();
        if (!$user instanceof User) {
            $user = null;
        }

        if (!$campusId && $user && $user->getCampus()) {
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

        $outings = $outingRepository->search($filters, $user);

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

    #[Route('/{id}/annuler', name: 'cancel', requirements: ['id' => '\d+'], methods: ['POST','GET'])]
    public function cancel(Outing $outing, EntityManagerInterface $em): Response
    {
        if ($outing->getOrganizer() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas annuler cette sortie.');
        }

        if ($outing->getStatus() !== Outing::ETAT_OUVERTE) {
            $this->addFlash('warning', 'Seules les sorties ouvertes peuvent être annulées.');
            return $this->redirectToRoute('outing_list');
        }

        $outing->setStatus(Outing::ETAT_ANNULEE);
        $em->flush();

        $this->addFlash('success', 'La sortie a été annulée.');
        return $this->redirectToRoute('outing_list');
    }

    #[Route('/{id}/inscription', name: 'register', requirements: ['id' => '\d+'], methods: ['POST','GET'])]
    public function register(Outing $outing, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // On ne peut s'inscrire que sur une sortie publiée (ouverte).
        if ($outing->getStatus() !== Outing::ETAT_OUVERTE) {
            $this->addFlash('warning', 'Vous ne pouvez vous inscrire que sur une sortie ouverte.');
            return $this->redirectToRoute('outing_list');
        }

        // Vérifier la date limite d'inscription
        $now = new \DateTimeImmutable();
        if ($outing->getRegistrationDeadline() < $now) {
            $this->addFlash('warning', "La date limite d'inscription est dépassée.");
            return $this->redirectToRoute('outing_list');
        }

        // Vérifier la capacité
        if ($outing->getRegistrations()->count() >= $outing->getMaxRegistrations()) {
            $this->addFlash('warning', 'Le nombre maximal de participants est atteint.');
            return $this->redirectToRoute('outing_list');
        }

        // Vérifier que l'utilisateur n'est pas déjà inscrit
        foreach ($outing->getRegistrations() as $registration) {
            if ($registration->getParticipant() === $user) {
                $this->addFlash('info', 'Vous êtes déjà inscrit à cette sortie.');
                return $this->redirectToRoute('outing_list');
            }
        }
        /** @var \App\Entity\User $user */
        $registration = new Registration();
        $registration->setParticipant($user);
        $registration->setOuting($outing);

        $em->persist($registration);
        $em->flush();

        $this->addFlash('success', 'Vous êtes inscrit à la sortie.');
        return $this->redirectToRoute('outing_list');
    }

    #[Route('/{id}/desistement', name: 'unregister', requirements: ['id' => '\d+'], methods: ['POST','GET'])]
    public function unregister(Outing $outing, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // On ne peut se désister que tant que la sortie n'a pas débuté
        $now = new \DateTimeImmutable();
        if ($outing->getStartDateTime() <= $now) {
            $this->addFlash('warning', 'Vous ne pouvez plus vous désister car la sortie a débuté.');
            return $this->redirectToRoute('outing_list');
        }

        // Trouver l'inscription de l'utilisateur
        $registrationToRemove = null;
        foreach ($outing->getRegistrations() as $registration) {
            if ($registration->getParticipant() === $user) {
                $registrationToRemove = $registration;
                break;
            }
        }

        if (!$registrationToRemove) {
            $this->addFlash('info', 'Vous n’êtes pas inscrit à cette sortie.');
            return $this->redirectToRoute('outing_list');
        }

        $em->remove($registrationToRemove);
        $em->flush();

        $this->addFlash('success', 'Vous vous êtes désisté de la sortie.');
        return $this->redirectToRoute('outing_list');
    }
}
