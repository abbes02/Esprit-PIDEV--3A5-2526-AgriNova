<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/evenements')]
class EvenementController extends AbstractController
{
    #[Route('', name: 'evenement_index', methods: ['GET'])]
    public function index(Request $request, EvenementRepository $repo): Response
    {
        $q = $request->query->getString('q');
        $evenements = $q ? $repo->search($q) : $repo->findAll();

        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return $this->render('evenement/_table.html.twig', ['evenements' => $evenements]);
        }

        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenements,
            'q'          => $q,
        ]);
    }

    #[Route('/pdf', name: 'evenement_pdf', methods: ['GET'])]
    public function pdf(Request $request, EvenementRepository $repo, PdfService $pdf): Response
    {
        $q = $request->query->getString('q');
        $evenements = $q ? $repo->search($q) : $repo->findAll();

        $html = $this->renderView('evenement/pdf.html.twig', ['evenements' => $evenements]);

        return $pdf->generateResponse($html, 'evenements.pdf');
    }

    #[Route('/new', name: 'evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($evenement);
            $em->flush();
            $this->addFlash('success', 'Événement créé avec succès.');
            return $this->redirectToRoute('evenement_index');
        }

        return $this->render('evenement/form.html.twig', [
            'form'  => $form,
            'title' => 'Nouvel événement',
        ]);
    }

    #[Route('/{id}', name: 'evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement): Response
    {
        return $this->render('evenement/show.html.twig', ['evenement' => $evenement]);
    }

    #[Route('/{id}/edit', name: 'evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Événement mis à jour.');
            return $this->redirectToRoute('evenement_index');
        }

        return $this->render('evenement/form.html.twig', [
            'form'  => $form,
            'title' => 'Modifier l\'événement',
        ]);
    }

    #[Route('/{id}/delete', name: 'evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evenement->getId(), $request->request->get('_token'))) {
            $em->remove($evenement);
            $em->flush();
            $this->addFlash('success', 'Événement supprimé.');
        }

        return $this->redirectToRoute('evenement_index');
    }
}
