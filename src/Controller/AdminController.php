<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Entity\City;
use App\Repository\CityRepository;
use App\Form\CityType;
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

        $query = $request->query->getString('q');
        $sort = $request->query->getString('sort', 'lastName'); // par défaut tri par nom
        $order = $request->query->getString('order', 'asc');

        if ($query) {
            $users = $userRepository->findBySearch($query, $sort; $order);
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

    #[Route('/admin/villes', name: 'app_admin_cities', methods: ['GET'])]
    public function cities(CityRepository $cityRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

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

    #[Route('/admin/villes/ajouter', name: 'app_admin_city_add', methods: ['GET', 'POST'])]
    public function addCity(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

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

    #[Route('/admin/villes/{id}/supprimer', name: 'app_admin_city_delete', methods: ['POST'])]
    public function deleteCity(City $city, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

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

    #[Route('/admin/villes/{id}/modifier', name: 'app_admin_city_edit', methods: ['GET', 'POST'])]
    public function editCity(City $city, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

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
