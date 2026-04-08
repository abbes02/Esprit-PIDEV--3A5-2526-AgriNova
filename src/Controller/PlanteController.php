<?php

namespace App\Controller;

use App\Entity\Plante;
use App\Entity\Utilisateur;
use App\Form\PlanteType;
use App\Repository\PlanteRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/plante')]
class PlanteController extends AbstractController
{
    public function __construct(
        private FormFactoryInterface $formFactory
    ) {
    }

    #[Route('/', name: 'app_plante_index', methods: ['GET'])]
    public function index(
        Request $request,
        PlanteRepository $planteRepository,
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
        $sort = $request->query->get('sort', 'idPlante');
        $direction = strtoupper($request->query->get('direction', 'ASC'));

        $plantes = $planteRepository->findByUserCriteria((int) $utilisateur->getIdUtilisateur(), $search, $sort, $direction);
        $stats = $planteRepository->getStatsByUser((int) $utilisateur->getIdUtilisateur());
        $sceneParcelles = [];
        $scenePlantes = [];
        $parcelleIndex = [];

        foreach ($plantes as $plante) {
            $parcelle = $plante->getParcelle();
            if ($parcelle === null) {
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
    public function exportPdf(
        Request $request,
        PlanteRepository $planteRepository,
        UtilisateurRepository $utilisateurRepository
    ): Response {
        if (($guard = $this->denyUnlessAgriculteur($request)) !== null) {
            return $guard;
        }

        $utilisateur = $this->getCurrentUtilisateur($request, $utilisateurRepository);
        if ($utilisateur === null) {
            return new RedirectResponse('/login');
        }

        $plantes = $planteRepository->findByUserCriteria((int) $utilisateur->getIdUtilisateur());
        $date = (new \DateTime())->format('Y-m-d');
        $html = $this->renderView('plante/pdf_export.html.twig', ['plantes' => $plantes]);

        $response = new Response($html);
        $response->headers->set('Content-Type', 'text/html; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="agrinova_plantes_' . $date . '.pdf"');

        return $response;
    }

    #[Route('/new', name: 'app_plante_new', methods: ['GET', 'POST'])]
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

        $plante = new Plante();
        $form = $this->formFactory->create(PlanteType::class, $plante, [
            'user_id' => (int) $utilisateur->getIdUtilisateur(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($plante);
            $em->flush();
            $this->addFlash('success', 'Plante creee avec succes.');

            return $this->redirectToRoute('app_plante_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('plante/new.html.twig', [
            'plante' => $plante,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idPlante}', name: 'app_plante_show', methods: ['GET'])]
    public function show(
        Request $request,
        int $idPlante,
        PlanteRepository $planteRepository,
        UtilisateurRepository $utilisateurRepository
    ): Response {
        if (($guard = $this->denyUnlessAgriculteur($request)) !== null) {
            return $guard;
        }

        $utilisateur = $this->getCurrentUtilisateur($request, $utilisateurRepository);
        if ($utilisateur === null) {
            return new RedirectResponse('/login');
        }

        $plante = $planteRepository->findOwnedById($idPlante, (int) $utilisateur->getIdUtilisateur());
        if ($plante === null) {
            throw $this->createNotFoundException('Plante non trouvee');
        }

        return $this->render('plante/show.html.twig', [
            'plante' => $plante,
        ]);
    }

    #[Route('/{idPlante}/edit', name: 'app_plante_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        int $idPlante,
        PlanteRepository $planteRepository,
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

        $plante = $planteRepository->findOwnedById($idPlante, (int) $utilisateur->getIdUtilisateur());
        if ($plante === null) {
            throw $this->createNotFoundException('Plante non trouvee');
        }

        $form = $this->formFactory->create(PlanteType::class, $plante, [
            'user_id' => (int) $utilisateur->getIdUtilisateur(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Plante modifiee avec succes.');

            return $this->redirectToRoute('app_plante_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('plante/edit.html.twig', [
            'plante' => $plante,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idPlante}', name: 'app_plante_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        int $idPlante,
        PlanteRepository $planteRepository,
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

        $plante = $planteRepository->findOwnedById($idPlante, (int) $utilisateur->getIdUtilisateur());
        if ($plante === null) {
            throw $this->createNotFoundException('Plante non trouvee');
        }

        $em->remove($plante);
        $em->flush();
        $this->addFlash('success', 'Plante supprimee avec succes.');

        return $this->redirectToRoute('app_plante_index', [], Response::HTTP_SEE_OTHER);
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
