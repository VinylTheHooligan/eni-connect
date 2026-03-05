<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        // Optionnel : sécurité supplémentaire côté contrôleur
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
    #[Route('/admin/utilisateurs', name: 'app_admin_users')]
    public function users(UserRepository $userRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $q = $request->query->getString('q');

        if ($q) {
            $users = $userRepository->findBySearch($q);
        } else {
            $users = $userRepository->findAll();
        }

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/utilisateurs/{id}/toggle', name: 'app_admin_user_toggle')]
    public function toggleUser(User $user, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user->setisActive(!$user->isActive());
        $em->flush();

        $this->addFlash('success', 'Statut de l\'utilisateur mis à jour.');
        return $this->redirectToRoute('app_admin_users');
    }
}
