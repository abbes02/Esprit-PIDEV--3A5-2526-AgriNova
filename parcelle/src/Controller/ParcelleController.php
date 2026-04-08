<?php

namespace App\Controller;

use App\Entity\Parcelle;
use App\Form\ParcelleType;
use App\Repository\ParcelleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/parcelle')]
class ParcelleController extends AbstractController
{
    public function __construct(
        private FormFactoryInterface $formFactory
    ) {
    }

    #[Route('/', name: 'app_parcelle_index', methods: ['GET'])]
    public function index(Request $request, ParcelleRepository $parcelleRepository): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'idParcelle');
        $direction = strtoupper($request->query->get('direction', 'ASC'));

        $parcelles = $parcelleRepository->findByCriteria($search, $sort, $direction);
        $stats = $parcelleRepository->getStats();

        return $this->render('parcelle/index.html.twig', [
            'parcelles' => $parcelles,
            'stats' => $stats,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'sceneParcelles' => array_map(static function (Parcelle $parcelle): array {
                return [
                    'id' => $parcelle->getIdParcelle(),
                    'proprietaire' => $parcelle->getProprietaire(),
                    'localisation' => $parcelle->getLocalisation(),
                    'longueur' => $parcelle->getLongueur(),
                    'largeur' => $parcelle->getLargeur(),
                    'superficie' => $parcelle->getSuperficie(),
                    'typeDeSol' => $parcelle->getTypeDeSol(),
                ];
            }, $parcelles),
        ]);
    }

    #[Route('/export-pdf', name: 'app_parcelle_export_pdf', methods: ['GET'])]
    public function exportPdf(ParcelleRepository $parcelleRepository): Response
    {
        $parcelles = $parcelleRepository->findAll();
        $date = (new \DateTime())->format('Y-m-d');
        $html = $this->renderView('parcelle/pdf_export.html.twig', ['parcelles' => $parcelles]);
        
        // TODO: Enable PDF after composer require knp/snappy
        $response = new Response($html);
        $response->headers->set('Content-Type', 'text/html; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="agrinova_parcelles_' . $date . '.pdf"');
        
        return $response;
    }

    #[Route('/new', name: 'app_parcelle_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $parcelle = new Parcelle();
        $form = $this->formFactory->create(ParcelleType::class, $parcelle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($parcelle);
            $em->flush();
            $this->addFlash('success', 'Parcelle créée avec succès!');

            return $this->redirectToRoute('app_parcelle_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('parcelle/new.html.twig', [
            'parcelle' => $parcelle,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idParcelle}', name: 'app_parcelle_show', methods: ['GET'])]
    public function show(?Parcelle $parcelle): Response
    {
        if (!$parcelle) {
            throw $this->createNotFoundException('Parcelle non trouvée');
        }

        return $this->render('parcelle/show.html.twig', [
            'parcelle' => $parcelle,
        ]);
    }

    #[Route('/{idParcelle}/edit', name: 'app_parcelle_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ?Parcelle $parcelle, EntityManagerInterface $em): Response
    {
        if (!$parcelle) {
            throw $this->createNotFoundException('Parcelle non trouvée');
        }

        $form = $this->formFactory->create(ParcelleType::class, $parcelle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Parcelle modifiée avec succès!');

            return $this->redirectToRoute('app_parcelle_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('parcelle/edit.html.twig', [
            'parcelle' => $parcelle,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idParcelle}', name: 'app_parcelle_delete', methods: ['POST'])]
    public function delete(Request $request, ?Parcelle $parcelle, EntityManagerInterface $em): Response
    {
        if (!$parcelle) {
            throw $this->createNotFoundException('Parcelle non trouvée');
        }

        $em->remove($parcelle);
        $em->flush();
        $this->addFlash('success', 'Parcelle supprimée avec succès!');

        return $this->redirectToRoute('app_parcelle_index', [], Response::HTTP_SEE_OTHER);
    }
}
?>

