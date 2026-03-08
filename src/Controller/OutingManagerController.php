<?php

namespace App\Controller;

use App\Entity\Outing;
use App\Entity\Registration;
use App\Entity\User;
use App\Form\CancelOutingType;
use App\Form\OutingType;
use App\Security\Voter\OutingManagerVoter;
use App\Services\OutingManagementService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ORGANIZER')]
#[Route('/sorties/gestion', name: 'manage_')]
final class OutingManagerController extends AbstractController
{

    public function __construct(
        private OutingManagementService $oms
    )
    {}

    #[Route('/creer', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {

        $this->denyAccessUnlessGranted(OutingManagerVoter::CREATE);

        /** @var User $user */
        $user = $this->getUser();

        $outing = $this->oms->initializeOuting($user, $this->isGranted('ROLE_ADMIN'));

        $form = $this->createForm(OutingType::class, $outing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
            {
            // Par défaut l’état est déjà ETAT_CREATION dans le constructeur
            $action = $request->request->get('action');

            if ($action === 'publish')
            {
                $this->oms->publish($outing);
                $this->addFlash('success', 'La sortie a bien été créée et publiée.');
            } else
            {
                // Par défaut l’état est déjà ETAT_CREATION dans le constructeur
                $this->addFlash('success', 'La sortie a bien été créée et est en cours de création.');
            }

            $registration = $this->oms->autoRegisterOrganizer($outing, $user);

            $this->oms->save([$outing, $registration]);

            return $this->redirectToRoute('outing_detail', ['id' => $outing->getId()]);
        }

        return $this->render('outing/create.html.twig', [
            'outingForm' => $form->createView(),
        ]);
    }

    #[Route('/{id}/modifier', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Outing $outing, Request $request): Response
    {
        $user = $this->getUser();

        $this->denyAccessUnlessGranted(OutingManagerVoter::EDIT, $outing);

        $form = $this->createForm(OutingType::class, $outing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $action = $request->request->get('action');

            if ($action === 'delete') {
                $this->denyAccessUnlessGranted(OutingManagerVoter::DELETE, $outing);
                $this->oms->delete($outing);
                $this->oms->save($outing);
                $this->addFlash('success', 'La sortie a bien été supprimée.');
                return $this->redirectToRoute('outing_list');
            }

            if ($action === 'publish') {
                $this->denyAccessUnlessGranted(OutingManagerVoter::PUBLISH, $outing);
                $this->oms->publish($outing);
                $this->addFlash('success', 'La sortie a été publiée !');
            } else {
                $this->addFlash('success', 'La sortie a bien été modifiée.');
            }

            $this->oms->save($outing);

            return $this->redirectToRoute('outing_detail', ['id' => $outing->getId()]);
        }

        return $this->render('outing/edit.html.twig', [
            'outingForm' => $form->createView(),
            'outing' => $outing,
        ]);
    }

    #[Route('/{id}/publier', name: 'publish', requirements: ['id' => '\d+'], methods: ['POST','GET'])]
    public function publish(Outing $outing): Response
    {
        $this->denyAccessUnlessGranted(OutingManagerVoter::PUBLISH, $outing);

        $this->oms->publish($outing);
        $this->oms->save($outing);

        $this->addFlash('success', 'La sortie a été publiée.');

        return $this->redirectToRoute('outing_list');
    }

    #[Route('/{id}/annuler', name: 'cancel', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function cancel(Outing $outing, Request $request, EntityManagerInterface $em): Response
    {

        $this->denyAccessUnlessGranted(OutingManagerVoter::CANCEL, $outing);

        $form = $this->createForm(CancelOutingType::class, $outing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $this->oms->cancel($outing);
            $this->oms->save($outing);

            $this->addFlash('success', 'La sortie a bien été annulé !');
            return $this->redirectToRoute('outing_list');
        }

        return $this->render('outing/cancel.html.twig', [
            'cancelForm' => $form->createView(),
            'outing' => $outing,
        ]);
    }
}
