<?php

namespace App\Controller\Admin;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Services\Admin\CampusManager;
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
    public function campuses(CampusManager $cm, Request $request): Response
    {

        $query = $request->query->getString('q');
        $sort = $request->query->getString('sort', 'name');
        $order = $request->query->getString('order', 'asc');

        $campuses = $cm->search($query, $sort, $order);

        return $this->render('admin/campuses.html.twig', [
            'campuses' => $campuses,
        ]);
    }

    #[Route('/ajouter', name: 'app_admin_campus_add', methods: ['GET', 'POST'])]
    public function addCampus(Request $request, CampusManager $cm): Response
    {

        $campus = new Campus();
        $form = $this->createForm(CampusType::class, $campus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cm->save($campus);
            $this->addFlash('success', 'Le campus a été ajouté.');
            return $this->redirectToRoute('app_admin_campuses');
        }

        return $this->render('admin/campus_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter un campus',
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_admin_campus_edit', methods: ['GET', 'POST'])]
    public function editCampus(Campus $campus, Request $request, CampusManager $cm): Response
    {
        $form = $this->createForm(CampusType::class, $campus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $cm->save($campus);
            $this->addFlash('success', 'Le campus a été modifié.');
            return $this->redirectToRoute('app_admin_campuses');
        }

        return $this->render('admin/campus_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier un campus',
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_admin_campus_delete', methods: ['POST'])]
    public function deleteCampus(Campus $campus, Request $request, CampusManager $cm): Response
    {
        if (!$this->isCsrfTokenValid('delete_campus_' . $campus->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_admin_campuses');
        }

        if ($cm->remove($campus)) $this->addFlash('success', 'Le campus a été supprimé.');
        else $this->addFlash('danger', 'Impossible de supprimer ce campus car il est utilisé.');

        return $this->redirectToRoute('app_admin_campuses');
    }
}