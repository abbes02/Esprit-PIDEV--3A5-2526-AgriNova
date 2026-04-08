<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/evenements')]
class EvenementController extends AbstractController
{
    #[Route('', name: 'evenement_index', methods: ['GET'])]
    public function index(Request $request, EvenementRepository $repo): Response
    {
        if (($guard = $this->denyUnlessAgriculteur($request)) !== null) {
            return $guard;
        }

        $q = $request->query->getString('q');
        $evenements = $q ? $repo->search($q) : $repo->findAll();

        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return $this->render('evenement/_table.html.twig', ['evenements' => $evenements]);
        }

        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenements,
            'q' => $q,
        ]);
    }

    #[Route('/pdf', name: 'evenement_pdf', methods: ['GET'])]
    public function pdf(Request $request, EvenementRepository $repo, PdfService $pdf): Response
    {
        if (($guard = $this->denyUnlessAgriculteur($request)) !== null) {
            return $guard;
        }

        $q = $request->query->getString('q');
        $evenements = $q ? $repo->search($q) : $repo->findAll();
        $html = $this->renderView('evenement/pdf.html.twig', ['evenements' => $evenements]);

        return $pdf->generateResponse($html, 'evenements.pdf');
    }

    #[Route('/new', name: 'evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        if (($guard = $this->denyUnlessAgriculteur($request)) !== null) {
            return $guard;
        }

        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($evenement);
            $em->flush();
            $this->addFlash('success', 'Evenement cree avec succes.');

            return $this->redirectToRoute('evenement_index');
        }

        return $this->render('evenement/form.html.twig', [
            'form' => $form,
            'title' => 'Nouvel evenement',
        ]);
    }

    #[Route('/{id}', name: 'evenement_show', methods: ['GET'])]
    public function show(Request $request, Evenement $evenement): Response
    {
        if (($guard = $this->denyUnlessAgriculteur($request)) !== null) {
            return $guard;
        }

        return $this->render('evenement/show.html.twig', ['evenement' => $evenement]);
    }

    #[Route('/{id}/edit', name: 'evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $em): Response
    {
        if (($guard = $this->denyUnlessAgriculteur($request)) !== null) {
            return $guard;
        }

        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Evenement modifie avec succes.');

            return $this->redirectToRoute('evenement_index');
        }

        return $this->render('evenement/form.html.twig', [
            'form' => $form,
            'title' => 'Modifier l evenement',
        ]);
    }

    #[Route('/{id}/delete', name: 'evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $em): Response
    {
        if (($guard = $this->denyUnlessAgriculteur($request)) !== null) {
            return $guard;
        }

        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), (string) $request->request->get('_token'))) {
            $em->remove($evenement);
            $em->flush();
            $this->addFlash('success', 'Evenement supprime.');
        }

        return $this->redirectToRoute('evenement_index');
    }

    private function denyUnlessAgriculteur(Request $request): ?RedirectResponse
    {
        $user = $request->getSession()->get('auth_user');

        if (!is_array($user)) {
            return new RedirectResponse('/login');
        }

        $role = strtoupper((string) ($user['role'] ?? ''));

        if ($role !== 'AGRICULTEUR') {
            return new RedirectResponse('/dashboard');
        }

        return null;
    }
}
