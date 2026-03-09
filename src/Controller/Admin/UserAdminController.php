<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserImportType;
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

    #[Route('/utilisateurs/import', name: 'app_admin_user_import', methods: ['GET', 'POST'])]
    public function importUsers(Request $request): Response
    {
        $form = $this->createForm(UserImportType::class);
        $form->handleRequest($request);

        $rows = null;

        if ($form->isSubmitted() && $form->isValid())
        {
            $file = $form->get('file')->getData();
            $hasHeader = (bool) $form->get('hasHeader')->getData();

            if ($file)
            {
                $path = $file->getRealPath();
                $handle = fopen($path, 'r');

                if ($handle === false)
                {
                    $this->addFlash('danger', 'Impossible de lire le fichier CSV.');
                } else {
                    // 1) lecture d’une première ligne brute pour détecter le séparateur
                    $firstLine = fgets($handle);
                    $commaCount = substr_count($firstLine, ',');
                    $semicolonCount = substr_count($firstLine, ';');
                    $delimiter = $semicolonCount > $commaCount ? ';' : ',';

                    // Retour au début du fichier pour lire proprement avec fgetcsv
                    rewind($handle);

                    if ($hasHeader)
                    {
                        // on saute la ligne d’en‑tête
                        fgetcsv($handle, 0, $delimiter);
                    }

                    $rows = [];

                    // La boucle Tant que pour : email, username, firstName, lastName, phone, campus, role
                    while (($data = fgetcsv($handle, 0, $delimiter)) !== false)
                    {
                        // Verifier si on a bien 7 colonnes
                        $data = array_pad($data, 7, null);

                        $rows[] = [
                            'email'     => $data[0],
                            'username'  => $data[1],
                            'firstName' => $data[2],
                            'lastName'  => $data[3],
                            'phone'     => $data[4],
                            'campus'    => $data[5],
                            'role'      => $data[6],
                        ];
                    }

                    fclose($handle);

                    if (empty($rows))
                    {
                        $this->addFlash('warning', 'Le fichier ne contient aucune ligne de données.');
                    }
                }
            }
        }

        return $this->render('admin/user_import.html.twig', [
            'form' => $form->createView(),
            'rows' => $rows,
        ]);
    }

}