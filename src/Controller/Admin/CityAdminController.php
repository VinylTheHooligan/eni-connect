<?php

namespace App\Controller\Admin;

use App\Entity\City;
use App\Form\CityType;
use App\Services\Admin\CityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/villes')]
class CityAdminController extends AbstractController
{

    #[Route('/', name: 'app_admin_cities', methods: ['GET'])]
    public function cities(CityManager $cm, Request $request): Response
    {
        $query = $request->query->getString('q');
        $sort = $request->query->getString('sort', 'name');
        $order = $request->query->getString('order', 'asc');

        $cities = $cm->search($query, $sort, $order);

        return $this->render('admin/cities.html.twig', [
            'cities' => $cities,
        ]);
    }

    #[Route('/ajouter', name: 'app_admin_city_add', methods: ['GET', 'POST'])]
    public function addCity(Request $request, CityManager $cm): Response
    {
        $city = new City();
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cm->save($city);
            $this->addFlash('success', 'La ville a été ajoutée.');
            return $this->redirectToRoute('app_admin_cities');
        }

        return $this->render('admin/city_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter une ville',
        ]);
    }

    #[Route('/ajouter-inline', name: 'app_admin_city_add_inline', methods: ['POST'])]
    public function addCityInline(Request $request, CityManager $cm): Response
    {
        if (!$this->isCsrfTokenValid('add_city_inline', $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_admin_cities');
        }

        $name = trim((string) $request->request->get('name', ''));
        $postalCode = trim((string) $request->request->get('postalCode', ''));

        if ($name === '' || $postalCode === '') {
            $this->addFlash('warning', 'Veuillez renseigner la ville et le code postal.');
            return $this->redirectToRoute('app_admin_cities');
        }

        $city = new City();
        $city->setName($name)->setPostalCode($postalCode);

        $cm->save($city);

        $this->addFlash('success', 'La ville a été ajoutée.');

        return $this->redirectToRoute('app_admin_cities');
    }

    #[Route('/{id}/supprimer', name: 'app_admin_city_delete', methods: ['POST'])]
    public function deleteCity(City $city, Request $request, CityManager $cm): Response
    {
        if (!$this->isCsrfTokenValid('delete_city_' . $city->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_admin_cities');
        }

        if ($cm->remove($city))
        {
            $this->addFlash('success', 'La ville a été supprimée.');
        } else
        {
            $this->addFlash('danger', 'Impossible de supprimer cette ville car elle est utilisée.');
        }

        return $this->redirectToRoute('app_admin_cities');
    }

    #[Route('/{id}/modifier', name: 'app_admin_city_edit', methods: ['GET', 'POST'])]
    public function editCity(City $city, Request $request, CityManager $cm): Response
    {
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cm->save($city);
            $this->addFlash('success', 'La ville a été modifiée.');
            return $this->redirectToRoute('app_admin_cities');
        }

        return $this->render('admin/city_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier une ville',
        ]);
    }
}