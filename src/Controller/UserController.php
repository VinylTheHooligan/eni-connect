<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')] // seuls les connectés accèdent au profil
final class UserController extends AbstractController
{
    // Tâche 3 - Gérer son profil
    #[Route('/profil', name: 'app_profil')]
    public function editProfil(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser(); // récupère l'utilisateur connecté

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Profil mis à jour !');
            return $this->redirectToRoute('app_profil');
        }

        return $this->render('user/profil.html.twig', [
            'form' => $form,
        ]);
    }
    // Tâche 6 - Afficher le profil d'un participant
    #[Route('/profil/{id}', name: 'app_profil_show')]
    public function showProfil(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }
}
