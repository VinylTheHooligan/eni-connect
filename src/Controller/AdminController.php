<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Entity\City;
use App\Repository\CityRepository;
use App\Form\CityType;
use App\Form\UserType;
use App\Form\UserImportType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Campus;
use App\Repository\CampusRepository;
use App\Form\CampusType;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/utilisateurs', name: 'app_admin_users')]
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

    #[Route('/utilisateurs/ajouter', name: 'app_admin_user_add', methods: ['GET', 'POST'])]
    public function addUser(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher): Response
        {
        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $user->setIsActive(true);

        $form = $this->createForm(UserType::class, $user, [
            'include_roles' => true,
            'default_role' => 'ROLE_USER',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            if ($user->getPlainPassword())
            {
                $hashedPassword = $passwordHasher->hashPassword($user, $user->getPlainPassword());
                $user->setPasswordHash($hashedPassword);
                $user->setPlainPassword(null);
            }

            // 1 seul rôle est stocké, l'héritage est géré par security.yaml
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

    #[Route('/utilisateurs/{id}/toggle', name: 'app_admin_user_toggle')]
    public function toggleUser(User $user, EntityManagerInterface $em): Response
    {
        $user->setisActive(!$user->isActive());
        $em->flush();

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
            'form' => $form,
            'rows' => $rows,
        ]);
    }

    #[Route('/villes', name: 'app_admin_cities', methods: ['GET'])]
    public function cities(CityRepository $cityRepository, Request $request): Response
    {
        $query = $request->query->getString('q');
        $sort = $request->query->getString('sort', 'name');
        $order = $request->query->getString('order', 'asc');

        $cities = $cityRepository->findBySearch($query, $sort, $order);

        return $this->render('admin/cities.html.twig', [
            'cities' => $cities,
        ]);
    }

    #[Route('/villes/ajouter', name: 'app_admin_city_add', methods: ['GET', 'POST'])]
    public function addCity(Request $request, EntityManagerInterface $em): Response
    {
        $city = new City();
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($city);
            $em->flush();

            $this->addFlash('success', 'La ville a été ajoutée.');

            return $this->redirectToRoute('app_admin_cities');
        }

        return $this->render('admin/city_form.html.twig', [
            'form' => $form,
            'title' => 'Ajouter une ville',
        ]);
    }

    #[Route('/villes/ajouter-inline', name: 'app_admin_city_add_inline', methods: ['POST'])]
    public function addCityInline(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('add_city_inline', $request->request->get('_token')))
        {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_admin_cities');
        }

        $name = trim((string) $request->request->get('name', ''));
        $postalCode = trim((string) $request->request->get('postalCode', ''));

        if ($name === '' || $postalCode === '')
        {
            $this->addFlash('warning', 'Veuillez renseigner la ville et le code postal.');
            return $this->redirectToRoute('app_admin_cities');
        }

        $city = new City();
        $city->setName($name);
        $city->setPostalCode($postalCode);

        $em->persist($city);
        $em->flush();

        $this->addFlash('success', 'La ville a été ajoutée.');

        return $this->redirectToRoute('app_admin_cities');
    }

    #[Route('/villes/{id}/supprimer', name: 'app_admin_city_delete', methods: ['POST'])]
    public function deleteCity(City $city, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete_city_' . $city->getId(), $request->request->get('_token')))
        {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_admin_cities');
        }

        try
        {
            $em->remove($city);
            $em->flush();
            $this->addFlash('success', 'La ville a été supprimée.');
        } catch (\Throwable $e)
        {
            // Par exemple si des lieux sont encore liés à cette ville
            $this->addFlash('danger', 'Impossible de supprimer cette ville car elle est utilisée.');
        }

        return $this->redirectToRoute('app_admin_cities');
    }

    #[Route('/villes/{id}/modifier', name: 'app_admin_city_edit', methods: ['GET', 'POST'])]
    public function editCity(City $city, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->flush();

            $this->addFlash('success', 'La ville a été modifiée.');
            return $this->redirectToRoute('app_admin_cities');
        }

        return $this->render('admin/city_form.html.twig', [
            'form' => $form,
            'title' => 'Modifier une ville',
        ]);
    }
    #[Route('/admin/campus', name: 'app_admin_campuses', methods: ['GET'])]
    public function campuses(CampusRepository $campusRepository, Request $request): Response
    {

        $query = $request->query->getString('q');
        $sort = $request->query->getString('sort', 'name');
        $order = $request->query->getString('order', 'asc');

        $campuses = $campusRepository->findBySearch($query, $sort, $order);

        return $this->render('admin/campuses.html.twig', [
            'campuses' => $campuses,
        ]);
    }
    #[Route('/admin/campus/{id}/modifier', name: 'app_admin_campus_edit', methods: ['GET', 'POST'])]
    public function editCampus(Campus $campus, Request $request, EntityManagerInterface $em): Response
    {

        $form = $this->createForm(CampusType::class, $campus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Le campus a été modifié.');
            return $this->redirectToRoute('app_admin_campuses');
        }

        return $this->render('admin/campus_form.html.twig', [
            'form' => $form,
            'title' => 'Modifier un campus',
        ]);
    }

    #[Route('/admin/campus/{id}/supprimer', name: 'app_admin_campus_delete', methods: ['POST'])]
    public function deleteCampus(Campus $campus, Request $request, EntityManagerInterface $em): Response
    {

        if (!$this->isCsrfTokenValid('delete_campus_' . $campus->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_admin_campuses');
        }

        try {
            $em->remove($campus);
            $em->flush();
            $this->addFlash('success', 'Le campus a été supprimé.');
        } catch (\Throwable $e) {
            $this->addFlash('danger', 'Impossible de supprimer ce campus car il est utilisé.');
        }

        return $this->redirectToRoute('app_admin_campuses');
    }
    #[Route('/admin/campus/ajouter', name: 'app_admin_campus_add', methods: ['GET', 'POST'])]
    public function addCampus(Request $request, EntityManagerInterface $em): Response
    {

        $campus = new Campus();
        $form = $this->createForm(CampusType::class, $campus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($campus);
            $em->flush();
            $this->addFlash('success', 'Le campus a été ajouté.');
            return $this->redirectToRoute('app_admin_campuses');
        }

        return $this->render('admin/campus_form.html.twig', [
            'form' => $form,
            'title' => 'Ajouter un campus',
        ]);
    }
}
