<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ParcelleRepository;
use App\Repository\PlanteRepository;

class HomepageController extends AbstractController
{
    public function __construct(
        private ParcelleRepository $parcelleRepository,
        private PlanteRepository $planteRepository
    ) {
    }

    #[Route('/', name: 'app_homepage')]
    public function index(): Response
    {
        return $this->render('homepage/index.html.twig', [
            'parcelleStats' => $this->parcelleRepository->getStats(),
            'planteStats' => $this->planteRepository->getStats(),
            'featuredParcelles' => $this->parcelleRepository->findBy([], ['idParcelle' => 'DESC'], 3),
            'featuredPlantes' => $this->planteRepository->findBy([], ['idPlante' => 'DESC'], 4),
        ]);
    }

    #[Route('/backoffice', name: 'app_backoffice')]
    public function backoffice(Request $request): Response
    {
        $search = $request->query->get('search');
        $entity = $request->query->get('entity', 'parcelles');
        $direction = strtoupper($request->query->get('direction', 'ASC'));
        $parcelleSort = $request->query->get('parcelle_sort', 'idParcelle');
        $planteSort = $request->query->get('plante_sort', 'idPlante');

        return $this->render('admin/backoffice.html.twig', [
            'parcelles' => $this->parcelleRepository->findByCriteria($entity === 'parcelles' ? $search : null, $parcelleSort, $direction),
            'plantes' => $this->planteRepository->findByCriteria($entity === 'plantes' ? $search : null, $planteSort, $direction),
            'parcelleStats' => $this->parcelleRepository->getStats(),
            'planteStats' => $this->planteRepository->getStats(),
            'search' => $search,
            'entity' => $entity,
            'direction' => $direction,
            'parcelle_sort' => $parcelleSort,
            'plante_sort' => $planteSort,
        ]);
    }
}


