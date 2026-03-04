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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[IsGranted('ROLE_USER')]
final class UserController extends AbstractController
{
    // Tâche 3 - Gérer son profil
    #[Route('/profil', name: 'app_profil')]
    public function edit(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Si un nouveau mot de passe a été saisi
            if ($user->getPlainPassword()) {
                $hashedPassword = $passwordHasher->hashPassword($user, $user->getPlainPassword());
                $user->setPasswordHash($hashedPassword);
                $user->setPlainPassword(null); // on efface le mot de passe en clair
            }

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
