<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Entity\City;
use App\Repository\CityRepository;
use App\Form\CityType;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin', name: 'app_admin_')]
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

    #[Route('/utilisateurs/{id}/toggle', name: 'app_admin_user_toggle')]
    public function toggleUser(User $user, EntityManagerInterface $em): Response
    {
        $user->setisActive(!$user->isActive());
        $em->flush();

        $this->addFlash('success', 'Statut de l\'utilisateur mis à jour.');
        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/villes', name: 'app_admin_cities', methods: ['GET'])]
    public function cities(CityRepository $cityRepository, Request $request): Response
    {
        $query = $request->query->getString('q');

        if ($query !== '') {
            $queryBuilder = $cityRepository->createQueryBuilder('c')
                ->where('LOWER(c.name) LIKE :query')
                ->setParameter('query', '%'.mb_strtolower($query).'%')
                ->orderBy('c.name', 'ASC');

            $cities = $queryBuilder->getQuery()->getResult();
        } else {
            $cities = $cityRepository->findBy([], ['name' => 'ASC']);
        }

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

        if ($form->isSubmitted() && $form->isValid()) {
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

    #[Route('/villes/{id}/supprimer', name: 'app_admin_city_delete', methods: ['POST'])]
    public function deleteCity(City $city, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete_city_' . $city->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_admin_cities');
        }

        try {
            $em->remove($city);
            $em->flush();
            $this->addFlash('success', 'La ville a été supprimée.');
        } catch (\Throwable $e) {
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

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'La ville a été modifiée.');
            return $this->redirectToRoute('app_admin_cities');
        }

        return $this->render('admin/city_form.html.twig', [
            'form' => $form,
            'title' => 'Modifier une ville',
        ]);
    }

}
