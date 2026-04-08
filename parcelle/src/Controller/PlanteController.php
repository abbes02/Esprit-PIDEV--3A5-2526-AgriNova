<?php

namespace App\Controller;

use App\Entity\Plante;
use App\Form\PlanteType;
use App\Repository\PlanteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// use Knp\Snappy\Pdf; // Enable after composer require knp/snappy
use Symfony\Component\Routing\Annotation\Route;

#[Route('/plante')]
class PlanteController extends AbstractController
{
    public function __construct(
        private FormFactoryInterface $formFactory
    ) {
    }

    #[Route('/', name: 'app_plante_index', methods: ['GET'])]
    public function index(Request $request, PlanteRepository $planteRepository): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'idPlante');
        $direction = strtoupper($request->query->get('direction', 'ASC'));

        $plantes = $planteRepository->findByCriteria($search, $sort, $direction);
        $stats = $planteRepository->getStats();
        $sceneParcelles = [];
        $scenePlantes = [];
        $parcelleIndex = [];

        foreach ($plantes as $plante) {
            $parcelle = $plante->getParcelle();
            if (!$parcelle) {
                continue;
            }

            $parcelleId = (string) $parcelle->getIdParcelle();
            if (!array_key_exists($parcelleId, $parcelleIndex)) {
                $parcelleIndex[$parcelleId] = count($sceneParcelles);
                $sceneParcelles[] = [
                    'id' => $parcelle->getIdParcelle(),
                    'proprietaire' => $parcelle->getProprietaire(),
                    'localisation' => $parcelle->getLocalisation(),
                    'longueur' => $parcelle->getLongueur(),
                    'largeur' => $parcelle->getLargeur(),
                    'superficie' => $parcelle->getSuperficie(),
                    'typeDeSol' => $parcelle->getTypeDeSol(),
                    'plantCount' => 0,
                    'plantNames' => [],
                ];
            }

            $sceneParcelles[$parcelleIndex[$parcelleId]]['plantCount'] += 1;
            if (count($sceneParcelles[$parcelleIndex[$parcelleId]]['plantNames']) < 4) {
                $sceneParcelles[$parcelleIndex[$parcelleId]]['plantNames'][] = $plante->getNom();
            }

            $scenePlantes[] = [
                'id' => $plante->getIdPlante(),
                'nom' => $plante->getNom(),
                'type' => $plante->getType(),
                'parcelleId' => $parcelle->getIdParcelle(),
            ];
        }

        return $this->render('plante/index.html.twig', [
            'plantes' => $plantes,
            'stats' => $stats,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'sceneParcelles' => $sceneParcelles,
            'scenePlantes' => $scenePlantes,
        ]);
    }

    #[Route('/export-pdf', name: 'app_plante_export_pdf', methods: ['GET'])]
    public function exportPdf(PlanteRepository $planteRepository): Response
    {
        $plantes = $planteRepository->findAll();
        $date = (new \DateTime())->format('Y-m-d');
        $html = $this->renderView('plante/pdf_export.html.twig', ['plantes' => $plantes]);
        
        // TODO: Enable PDF after composer require knp/snappy
        $response = new Response($html);
        $response->headers->set('Content-Type', 'text/html; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="agrinova_plantes_' . $date . '.pdf"');
        // $response->headers->set('Content-Length', strlen($pdfContent));
        
        return $response;
    }

    #[Route('/new', name: 'app_plante_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $plante = new Plante();
        $form = $this->formFactory->create(PlanteType::class, $plante);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($plante);
            $em->flush();
            $this->addFlash('success', 'Plante créée avec succès!');

            return $this->redirectToRoute('app_plante_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('plante/new.html.twig', [
            'plante' => $plante,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idPlante}', name: 'app_plante_show', methods: ['GET'])]
    public function show(?Plante $plante): Response
    {
        if (!$plante) {
            throw $this->createNotFoundException('Plante non trouvée');
        }

        return $this->render('plante/show.html.twig', [
            'plante' => $plante,
        ]);
    }

    #[Route('/{idPlante}/edit', name: 'app_plante_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ?Plante $plante, EntityManagerInterface $em): Response
    {
        if (!$plante) {
            throw $this->createNotFoundException('Plante non trouvée');
        }

        $form = $this->formFactory->create(PlanteType::class, $plante);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Plante modifiée avec succès!');

            return $this->redirectToRoute('app_plante_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('plante/edit.html.twig', [
            'plante' => $plante,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idPlante}', name: 'app_plante_delete', methods: ['POST'])]
    public function delete(Request $request, ?Plante $plante, EntityManagerInterface $em): Response
    {
        if (!$plante) {
            throw $this->createNotFoundException('Plante non trouvée');
        }

        $em->remove($plante);
        $em->flush();
        $this->addFlash('success', 'Plante supprimée avec succès!');

        return $this->redirectToRoute('app_plante_index', [], Response::HTTP_SEE_OTHER);
    }
}
?>

