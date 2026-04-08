<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/formations')]
class FormationController extends AbstractController
{
    #[Route('', name: 'formation_index', methods: ['GET'])]
    public function index(Request $request, FormationRepository $repo): Response
    {
        $q = $request->query->getString('q');
        $formations = $q ? $repo->search($q) : $repo->findAll();

        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return $this->render('formation/_table.html.twig', ['formations' => $formations]);
        }

        return $this->render('formation/index.html.twig', [
            'formations' => $formations,
            'q'          => $q,
        ]);
    }

    #[Route('/pdf', name: 'formation_pdf', methods: ['GET'])]
    public function pdf(Request $request, FormationRepository $repo, PdfService $pdf): Response
    {
        $q = $request->query->getString('q');
        $formations = $q ? $repo->search($q) : $repo->findAll();

        $html = $this->renderView('formation/pdf.html.twig', ['formations' => $formations]);

        return $pdf->generateResponse($html, 'formations.pdf');
    }

    #[Route('/new', name: 'formation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($formation);
            $em->flush();
            $this->addFlash('success', 'Formation créée avec succès.');
            return $this->redirectToRoute('formation_index');
        }

        return $this->render('formation/form.html.twig', [
            'form'  => $form,
            'title' => 'Nouvelle formation',
        ]);
    }

    #[Route('/{id}', name: 'formation_show', methods: ['GET'])]
    public function show(Formation $formation): Response
    {
        return $this->render('formation/show.html.twig', ['formation' => $formation]);
    }

    #[Route('/{id}/edit', name: 'formation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Formation $formation, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Formation mise à jour.');
            return $this->redirectToRoute('formation_index');
        }

        return $this->render('formation/form.html.twig', [
            'form'  => $form,
            'title' => 'Modifier la formation',
        ]);
    }

    #[Route('/{id}/delete', name: 'formation_delete', methods: ['POST'])]
    public function delete(Request $request, Formation $formation, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$formation->getId(), $request->request->get('_token'))) {
            $em->remove($formation);
            $em->flush();
            $this->addFlash('success', 'Formation supprimée.');
        }

        return $this->redirectToRoute('formation_index');
    }
}
