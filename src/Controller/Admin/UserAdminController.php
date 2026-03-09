<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/utilisateurs')]
class UserAdminController extends AbstractController
{

    #[Route('/', name: 'app_admin_users')]
    public function users(UserRepository $userRepository, Request $request): Response
    {
        $query = $request->query->getString('q');
        $sort = $request->query->getString('sort', 'lastName'); // par défaut tri par nom
        $order = $request->query->getString('order', 'asc');

        $users = $userRepository->findBySearch($query, $sort, $order);

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/ajouter', name: 'app_admin_user_add', methods: ['GET', 'POST'])]
    public function addUser(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {

        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $user->setIsActive(true);

        $form = $this->createForm(UserType::class, $user, [
            'include_roles' => true,
            'default_role' => 'ROLE_USER',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user->getPlainPassword()) {
                $hashedPassword = $passwordHasher->hashPassword($user, $user->getPlainPassword());
                $user->setPasswordHash($hashedPassword);
                $user->setPlainPassword(null);
            }

            // 1 seul rôle stocké, l'héritage est géré par security.yaml
            $selectedRole = $form->get('mainRole')->getData() ?? 'ROLE_USER';
            $user->setRoles([$selectedRole]);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'L\'utilisateur a été ajouté.');

            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/user_form.html.twig', [
            'form' => $form,
            'title' => 'Ajouter un utilisateur',
        ]);
    }

    #[Route('/{id}/toggle', name: 'app_admin_user_toggle')]
    public function toggleUser(User $user, EntityManagerInterface $em): Response
    {
        $user->setisActive(!$user->isActive());
        $em->flush();

        $this->addFlash('success', 'Statut de l\'utilisateur mis à jour.');
        return $this->redirectToRoute('app_admin_users');
    }
}