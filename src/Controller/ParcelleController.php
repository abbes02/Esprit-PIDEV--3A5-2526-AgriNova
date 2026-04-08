<?php

namespace App\Controller;

use App\Entity\Parcelle;
use App\Entity\Utilisateur;
use App\Form\ParcelleType;
use App\Repository\ParcelleRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    public function index(
        Request $request,
        ParcelleRepository $parcelleRepository,
        UtilisateurRepository $utilisateurRepository
    ): Response {
        if (($guard = $this->denyUnlessAgriculteur($request)) !== null) {
            return $guard;
        }

        $utilisateur = $this->getCurrentUtilisateur($request, $utilisateurRepository);
        if ($utilisateur === null) {
            return new RedirectResponse('/login');
        }

        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'idParcelle');
        $direction = strtoupper($request->query->get('direction', 'ASC'));

        $parcelles = $parcelleRepository->findByUserCriteria((int) $utilisateur->getIdUtilisateur(), $search, $sort, $direction);
        $stats = $parcelleRepository->getStatsByUser((int) $utilisateur->getIdUtilisateur());

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
    public function exportPdf(
        Request $request,
        ParcelleRepository $parcelleRepository,
        UtilisateurRepository $utilisateurRepository
    ): Response {
        if (($guard = $this->denyUnlessAgriculteur($request)) !== null) {
            return $guard;
        }

        $utilisateur = $this->getCurrentUtilisateur($request, $utilisateurRepository);
        if ($utilisateur === null) {
            return new RedirectResponse('/login');
        }

        $parcelles = $parcelleRepository->findByUserCriteria((int) $utilisateur->getIdUtilisateur());
        $date = (new \DateTime())->format('Y-m-d');
        $html = $this->renderView('parcelle/pdf_export.html.twig', ['parcelles' => $parcelles]);

        $response = new Response($html);
        $response->headers->set('Content-Type', 'text/html; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="agrinova_parcelles_' . $date . '.pdf"');

        return $response;
    }

    #[Route('/new', name: 'app_parcelle_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UtilisateurRepository $utilisateurRepository
    ): Response {
        if (($guard = $this->denyUnlessAgriculteur($request)) !== null) {
            return $guard;
        }

        $utilisateur = $this->getCurrentUtilisateur($request, $utilisateurRepository);
        if ($utilisateur === null) {
            return new RedirectResponse('/login');
        }

        $parcelle = new Parcelle();
        $this->synchronizeOwner($parcelle, $utilisateur);
        $form = $this->formFactory->create(ParcelleType::class, $parcelle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->synchronizeOwner($parcelle, $utilisateur);
            $em->persist($parcelle);
            $em->flush();
            $this->addFlash('success', 'Parcelle creee avec succes.');

            return $this->redirectToRoute('app_parcelle_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('parcelle/new.html.twig', [
            'parcelle' => $parcelle,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idParcelle}', name: 'app_parcelle_show', methods: ['GET'])]
    public function show(
        Request $request,
        int $idParcelle,
        ParcelleRepository $parcelleRepository,
        UtilisateurRepository $utilisateurRepository
    ): Response {
        if (($guard = $this->denyUnlessAgriculteur($request)) !== null) {
            return $guard;
        }

        $utilisateur = $this->getCurrentUtilisateur($request, $utilisateurRepository);
        if ($utilisateur === null) {
            return new RedirectResponse('/login');
        }

        $parcelle = $parcelleRepository->findOwnedById($idParcelle, (int) $utilisateur->getIdUtilisateur());

        if (!$parcelle) {
            throw $this->createNotFoundException('Parcelle non trouvee');
        }

        return $this->render('parcelle/show.html.twig', [
            'parcelle' => $parcelle,
        ]);
    }

    #[Route('/{idParcelle}/edit', name: 'app_parcelle_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        int $idParcelle,
        ParcelleRepository $parcelleRepository,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $em
    ): Response {
        if (($guard = $this->denyUnlessAgriculteur($request)) !== null) {
            return $guard;
        }

        $utilisateur = $this->getCurrentUtilisateur($request, $utilisateurRepository);
        if ($utilisateur === null) {
            return new RedirectResponse('/login');
        }

        $parcelle = $parcelleRepository->findOwnedById($idParcelle, (int) $utilisateur->getIdUtilisateur());

        if (!$parcelle) {
            throw $this->createNotFoundException('Parcelle non trouvee');
        }

        $form = $this->formFactory->create(ParcelleType::class, $parcelle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->synchronizeOwner($parcelle, $utilisateur);
            $em->flush();
            $this->addFlash('success', 'Parcelle modifiee avec succes.');

            return $this->redirectToRoute('app_parcelle_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('parcelle/edit.html.twig', [
            'parcelle' => $parcelle,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idParcelle}', name: 'app_parcelle_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        int $idParcelle,
        ParcelleRepository $parcelleRepository,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $em
    ): Response {
        if (($guard = $this->denyUnlessAgriculteur($request)) !== null) {
            return $guard;
        }

        $utilisateur = $this->getCurrentUtilisateur($request, $utilisateurRepository);
        if ($utilisateur === null) {
            return new RedirectResponse('/login');
        }

        $parcelle = $parcelleRepository->findOwnedById($idParcelle, (int) $utilisateur->getIdUtilisateur());

        if (!$parcelle) {
            throw $this->createNotFoundException('Parcelle non trouvee');
        }

        $em->remove($parcelle);
        $em->flush();
        $this->addFlash('success', 'Parcelle supprimee avec succes.');

        return $this->redirectToRoute('app_parcelle_index', [], Response::HTTP_SEE_OTHER);
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

    private function synchronizeOwner(Parcelle $parcelle, Utilisateur $utilisateur): void
    {
        $parcelle->setUtilisateur($utilisateur);
        $displayName = $utilisateur->getDisplayName();
        $parcelle->setProprietaire($displayName !== '' ? $displayName : (string) $utilisateur->getEmail());
    }
}
