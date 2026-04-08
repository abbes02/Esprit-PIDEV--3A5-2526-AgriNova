<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ParcelleRepository;
use App\Repository\PlanteRepository;
use App\Repository\UtilisateurRepository;

class HomepageController extends AbstractController
{
    public function __construct(
        private ParcelleRepository $parcelleRepository,
        private PlanteRepository $planteRepository
    ) {
    }

    #[Route('/agriculteur/home', name: 'app_homepage', methods: ['GET'])]
    public function index(Request $request, UtilisateurRepository $utilisateurRepository): Response
    {
        if (($guard = $this->denyUnlessAgriculteur($request)) !== null) {
            return $guard;
        }

        $utilisateur = $this->getCurrentUtilisateur($request, $utilisateurRepository);
        if ($utilisateur === null) {
            return new RedirectResponse('/login');
        }

        $userId = (int) $utilisateur->getIdUtilisateur();
        $featuredParcelles = array_slice($this->parcelleRepository->findByUserCriteria($userId, null, 'idParcelle', 'DESC'), 0, 3);
        $featuredPlantes = array_slice($this->planteRepository->findByUserCriteria($userId, null, 'idPlante', 'DESC'), 0, 4);

        return $this->render('homepage/index.html.twig', [
            'parcelleStats' => $this->parcelleRepository->getStatsByUser($userId),
            'planteStats' => $this->planteRepository->getStatsByUser($userId),
            'featuredParcelles' => $featuredParcelles,
            'featuredPlantes' => $featuredPlantes,
        ]);
    }

    #[Route('/agriculteur/backoffice', name: 'app_backoffice', methods: ['GET'])]
    public function backoffice(Request $request): Response
    {
        if (($guard = $this->denyUnlessAgriculteur($request)) !== null) {
            return $guard;
        }

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

    private function getCurrentUtilisateur(Request $request, UtilisateurRepository $utilisateurRepository): ?Utilisateur
    {
        $user = $request->getSession()->get('auth_user');
        $userId = (int) ($user['id'] ?? 0);

        if ($userId <= 0) {
            return null;
        }

        return $utilisateurRepository->find($userId);
    }
}


