<?php

namespace App\Controller\Admin;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/campus')]
class CampusAdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_campuses', methods: ['GET'])]
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
    #[Route('/{id}/modifier', name: 'app_admin_campus_edit', methods: ['GET', 'POST'])]
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

    #[Route('/{id}/supprimer', name: 'app_admin_campus_delete', methods: ['POST'])]
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
    #[Route('/ajouter', name: 'app_admin_campus_add', methods: ['GET', 'POST'])]
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