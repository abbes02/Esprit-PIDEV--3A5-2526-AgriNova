<?php

namespace App\Controller;

use App\Entity\Materiel;
use App\Entity\Panne;
use App\Entity\Utilisateur;
use App\Form\PanneType;
use App\Repository\LocationRepository;
use App\Repository\MaterielRepository;
use App\Repository\PanneRepository;
use App\Service\MaterielStateManager;
use App\Service\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panne')]
final class PanneController extends AbstractController
{
    public function __construct(
        private readonly UserContext $userContext,
        private readonly MaterielStateManager $materielStateManager,
    ) {
    }

    #[Route('/', name: 'app_panne_index', methods: ['GET'])]
    public function index(PanneRepository $panneRepository, LocationRepository $locationRepository): Response
    {
        $currentUser = $this->userContext->requireCurrentUser();
        $currentUserId = (int) $currentUser->getId();
        $pannes = $panneRepository->findAccessibleByUserId($currentUserId);

        return $this->render('panne/index.html.twig', [
            'pannes' => $pannes,
            'stats' => [
                'records' => count($pannes),
                'active_locations' => $locationRepository->countActiveByUtilisateurId($currentUserId),
                'unresolved_pannes' => $panneRepository->countUnresolvedByAccessibleUserId($currentUserId),
            ],
            'current_user' => $currentUser,
        ]);
    }

    #[Route('/new', name: 'app_panne_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MaterielRepository $materielRepository, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->userContext->requireCurrentUser();
        $currentUserId = (int) $currentUser->getId();
        $selectedMaterielId = max(0, (int) $request->query->get('materiel_id', 0));
        $materielChoices = $materielRepository->findReportableForUser($currentUserId);

        $panne = new Panne();
        $panne->setDatePanne(new \DateTime('today'));
        $selectedMateriel = null;

        if ($selectedMaterielId > 0) {
            foreach ($materielChoices as $choice) {
                if ($choice->getId() === $selectedMaterielId) {
                    $selectedMateriel = $choice;
                    break;
                }
            }
        }

        $lockMateriel = $selectedMateriel instanceof Materiel;

        if ($lockMateriel) {
            $materielChoices = [$selectedMateriel];
            $panne->setMateriel($selectedMateriel);
        }

        $formBuilder = $this->createFormBuilder($panne);
        PanneType::build($formBuilder, [
            'materiel_choices' => $materielChoices,
            'lock_materiel' => $lockMateriel,
        ]);
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($lockMateriel) {
            $panne->setMateriel($selectedMateriel);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->applyPanneBusinessRules($form, $panne, $materielRepository, $currentUserId);

            if (!$form->isValid()) {
                return $this->render('panne/new.html.twig', [
                    'panne' => $panne,
                    'form' => $form,
                    'current_user' => $currentUser,
                    'selected_materiel' => $selectedMateriel,
                    'lock_materiel' => $lockMateriel,
                ]);
            }

            $panne->setReportedByName(trim(($currentUser->getPrenom() ?? '') . ' ' . ($currentUser->getNom() ?? '')));

            $entityManager->persist($panne);
            $entityManager->flush();

            if ($panne->getMateriel() instanceof Materiel) {
                $this->materielStateManager->refreshForMateriel($panne->getMateriel());
            }

            $this->addFlash('success', 'Panne signalée avec succès.');

            return $this->redirectToRoute('app_panne_index', ['user_id' => $currentUser->getId()]);
        }

        return $this->render('panne/new.html.twig', [
            'panne' => $panne,
            'form' => $form,
            'current_user' => $currentUser,
            'selected_materiel' => $selectedMateriel,
            'lock_materiel' => $lockMateriel,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_panne_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Panne $panne, PanneRepository $panneRepository, MaterielRepository $materielRepository, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->userContext->requireCurrentUser();
        $currentUserId = (int) $currentUser->getId();
        $this->assertAccessibleByCurrentUser($panne, $currentUser, $panneRepository);

        $materielChoices = $materielRepository->findReportableForUser($currentUserId);
        // Ensure the panne's current materiel is always in the list
        $currentMateriel = $panne->getMateriel();
        if ($currentMateriel instanceof Materiel) {
            $found = false;
            foreach ($materielChoices as $c) {
                if ($c->getId() === $currentMateriel->getId()) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $materielChoices[] = $currentMateriel;
            }
        }

        $formBuilder = $this->createFormBuilder($panne);
        PanneType::build($formBuilder, [
            'materiel_choices' => $materielChoices,
            'lock_materiel' => false,
        ]);
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->applyPanneBusinessRules($form, $panne, $materielRepository, $currentUserId);

            if (!$form->isValid()) {
                return $this->render('panne/edit.html.twig', [
                    'panne' => $panne,
                    'form' => $form,
                    'current_user' => $currentUser,
                ]);
            }

            $entityManager->flush();

            if ($panne->getMateriel() instanceof Materiel) {
                $this->materielStateManager->refreshForMateriel($panne->getMateriel());
            }

            $this->addFlash('success', 'Panne mise à jour avec succès.');

            return $this->redirectToRoute('app_panne_show', ['id' => $panne->getId(), 'user_id' => $currentUser->getId()]);
        }

        return $this->render('panne/edit.html.twig', [
            'panne' => $panne,
            'form' => $form,
            'current_user' => $currentUser,
        ]);
    }

    #[Route('/{id}', name: 'app_panne_show', methods: ['GET'])]
    public function show(Panne $panne, PanneRepository $panneRepository): Response
    {
        $currentUser = $this->userContext->requireCurrentUser();
        $this->assertAccessibleByCurrentUser($panne, $currentUser, $panneRepository);

        return $this->render('panne/show.html.twig', [
            'panne' => $panne,
            'current_user' => $currentUser,
        ]);
    }

    #[Route('/{id}', name: 'app_panne_delete', methods: ['POST'])]
    public function delete(Request $request, Panne $panne, PanneRepository $panneRepository, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->userContext->requireCurrentUser();
        $this->assertAccessibleByCurrentUser($panne, $currentUser, $panneRepository);

        if ($this->isCsrfTokenValid('delete_panne_' . $panne->getId(), (string) $request->request->get('_token'))) {
            $materiel = $panne->getMateriel();

            $entityManager->remove($panne);
            $entityManager->flush();

            if ($materiel instanceof Materiel) {
                $this->materielStateManager->refreshForMateriel($materiel);
            }

            $this->addFlash('success', 'Panne supprimée avec succès.');
        }

        return $this->redirectToRoute('app_panne_index', ['user_id' => $currentUser->getId()]);
    }

    private function assertAccessibleByCurrentUser(Panne $panne, Utilisateur $currentUser, PanneRepository $panneRepository): void
    {
        if (!$panneRepository->isAccessibleByUser((int) $panne->getId(), (int) $currentUser->getId())) {
            throw $this->createNotFoundException();
        }
    }

    private function applyPanneBusinessRules(FormInterface $form, Panne $panne, MaterielRepository $materielRepository, int $currentUserId): void
    {
        if ($panne->getDatePanne() === null) {
            $panne->setDatePanne(new \DateTime('today'));
        }

        $selectedMaterielId = (int) ($panne->getMateriel()?->getId() ?? 0);

        if ($selectedMaterielId <= 0 || !$materielRepository->isReportableByUser($selectedMaterielId, $currentUserId)) {
            $form->get('materiel')->addError(new FormError('Vous ne pouvez déclarer une panne que sur vos matériels ou ceux loués en ce moment.'));
        }
    }
}
