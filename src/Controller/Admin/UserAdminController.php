<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Services\Admin\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/utilisateurs')]
class UserAdminController extends AbstractController
{

    #[Route('/', name: 'app_admin_users')]
    public function users(UserManager $um, Request $request): Response
    {
        $query = $request->query->getString('q');
        $sort = $request->query->getString('sort', 'lastName'); // par défaut tri par nom
        $order = $request->query->getString('order', 'asc');

        $users = $um->search($query, $sort, $order);

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/ajouter', name: 'app_admin_user_add', methods: ['GET', 'POST'])]
    public function addUser(Request $request, UserManager $um): Response {

        $user = new User();
        $user->setRoles(['ROLE_USER'])->setIsActive(true);

        $form = $this->createForm(UserType::class, $user, [
            'include_roles' => true,
            'default_role' => 'ROLE_USER',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 1 seul rôle stocké, l'héritage est géré par security.yaml
            $selectedRole = $form->get('mainRole')->getData() ?? 'ROLE_USER';
            $um->save($user, $selectedRole);

            $this->addFlash('success', 'L\'utilisateur a été ajouté.');

            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/user_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter un utilisateur',
        ]);
    }

    #[Route('/{id}/toggle', name: 'app_admin_user_toggle')]
    public function toggleUser(User $user, UserManager $um): Response
    {
        $um->toggle($user);

        $this->addFlash('success', 'Statut de l\'utilisateur mis à jour.');
        return $this->redirectToRoute('app_admin_users');
    }
}