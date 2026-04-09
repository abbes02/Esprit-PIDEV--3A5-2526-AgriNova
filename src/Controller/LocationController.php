<?php

namespace App\Controller;

use App\Entity\Location;
use App\Entity\Materiel;
use App\Entity\Utilisateur;
use App\Form\LocationType;
use App\Repository\LocationRepository;
use App\Repository\MaterielRepository;
use App\Repository\PanneRepository;
use App\Service\LocationStatusManager;
use App\Service\MaterielStateManager;
use App\Service\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/location')]
final class LocationController extends AbstractController
{
    public function __construct(
        private readonly UserContext $userContext,
        private readonly MaterielStateManager $materielStateManager,
        private readonly LocationStatusManager $locationStatusManager,
    ) {
    }

    #[Route('/', name: 'app_location_index', methods: ['GET'])]
    public function index(LocationRepository $locationRepository, PanneRepository $panneRepository): Response
    {
        $currentUser = $this->userContext->requireCurrentUser();
        $currentUserId = (int) $currentUser->getId();
        $locations = $locationRepository->findByUtilisateurId($currentUserId);

        if ($this->locationStatusManager->applyAutomaticStatusForMany($locations)) {
            $this->refreshMaterielStatesFromLocations($locations);
        }

        return $this->render('location/index.html.twig', [
            'locations' => $locations,
            'stats' => [
                'records' => count($locations),
                'active_locations' => $locationRepository->countActiveByUtilisateurId($currentUserId),
                'unresolved_pannes' => $panneRepository->countUnresolvedByOwnerId($currentUserId),
            ],
            'current_user' => $currentUser,
        ]);
    }

    #[Route('/new', name: 'app_location_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MaterielRepository $materielRepository, LocationRepository $locationRepository, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->userContext->requireCurrentUser();
        $selectedMaterielId = max(0, (int) $request->query->get('materiel_id', 0));
        $materielChoices = $materielRepository->findAvailableForLocation($selectedMaterielId > 0 ? $selectedMaterielId : null);

        $location = new Location();
        $location->setUtilisateur($currentUser);
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
            $location->setMateriel($selectedMateriel);

            if ($location->getMontantTotal() === null && $selectedMateriel->getPrixLocation() !== null) {
                $location->setMontantTotal($selectedMateriel->getPrixLocation());
            }
        }

        $formBuilder = $this->createFormBuilder($location);
        LocationType::build($formBuilder, [
            'materiel_choices' => $materielChoices,
            'lock_materiel' => $lockMateriel,
        ]);
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($lockMateriel) {
            // Enforce selected marketplace item even if the request is tampered with.
            $location->setMateriel($selectedMateriel);
        }

        $this->syncComputedAmount($location);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->applyLocationBusinessRules(
                $form,
                $location,
                $currentUser,
                $locationRepository,
                $lockMateriel,
                $selectedMateriel,
            );

            if (!$form->isValid()) {
                return $this->render('location/new.html.twig', [
                    'location' => $location,
                    'form' => $form,
                    'current_user' => $currentUser,
                    'selected_materiel' => $selectedMateriel,
                    'lock_materiel' => $lockMateriel,
                ]);
            }

            $location->setUtilisateur($currentUser);
            $this->locationStatusManager->applyAutomaticStatus($location);

            $entityManager->persist($location);
            $entityManager->flush();

            $this->refreshMaterielState($location->getMateriel());
            $this->addFlash('success', 'Location creee avec succes.');

            return $this->redirectToRoute('app_location_index', ['user_id' => $currentUser->getId()]);
        }

        return $this->render('location/new.html.twig', [
            'location' => $location,
            'form' => $form,
            'current_user' => $currentUser,
            'selected_materiel' => $selectedMateriel,
            'lock_materiel' => $lockMateriel,
        ]);
    }

    #[Route('/{id}', name: 'app_location_show', methods: ['GET'])]
    public function show(Location $location): Response
    {
        $currentUser = $this->userContext->requireCurrentUser();
        $this->assertOwnedByCurrentUser($location, $currentUser);

        if ($this->locationStatusManager->applyAutomaticStatus($location)) {
            $this->refreshMaterielState($location->getMateriel());
        }

        return $this->render('location/show.html.twig', [
            'location' => $location,
            'current_user' => $currentUser,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_location_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Location $location, MaterielRepository $materielRepository, LocationRepository $locationRepository, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->userContext->requireCurrentUser();
        $this->assertOwnedByCurrentUser($location, $currentUser);

        if ($location->getStatut() === Location::STATUT_ANNULEE) {
            $this->addFlash('error', 'Une location annulee ne peut plus etre modifiee.');

            return $this->redirectToRoute('app_location_index', ['user_id' => $currentUser->getId()]);
        }

        $previousMateriel = $location->getMateriel();
        $currentMaterielId = $location->getMateriel()?->getId();

        $formBuilder = $this->createFormBuilder($location);
        LocationType::build($formBuilder, [
            'materiel_choices' => $materielRepository->findAvailableForLocation($currentMaterielId),
        ]);
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        $this->syncComputedAmount($location);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->applyLocationBusinessRules(
                $form,
                $location,
                $currentUser,
                $locationRepository,
                false,
                null,
                (int) $location->getId(),
            );

            if (!$form->isValid()) {
                return $this->render('location/edit.html.twig', [
                    'location' => $location,
                    'form' => $form,
                    'current_user' => $currentUser,
                ]);
            }

            $this->locationStatusManager->applyAutomaticStatus($location);
            $entityManager->flush();

            if ($previousMateriel instanceof Materiel && $previousMateriel->getId() !== $location->getMateriel()?->getId()) {
                $this->refreshMaterielState($previousMateriel);
            }

            $this->refreshMaterielState($location->getMateriel());

            $this->addFlash('success', 'Location mise a jour avec succes.');

            return $this->redirectToRoute('app_location_index', ['user_id' => $currentUser->getId()]);
        }

        return $this->render('location/edit.html.twig', [
            'location' => $location,
            'form' => $form,
            'current_user' => $currentUser,
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_location_cancel', methods: ['POST'])]
    public function cancel(Request $request, Location $location): Response
    {
        $currentUser = $this->userContext->requireCurrentUser();
        $this->assertOwnedByCurrentUser($location, $currentUser);

        if ($this->isCsrfTokenValid('cancel_location_' . $location->getId(), (string) $request->request->get('_token'))) {
            if ($this->locationStatusManager->cancel($location)) {
                $this->refreshMaterielState($location->getMateriel());
                $this->addFlash('success', 'Location annulee avec succes.');
            } else {
                $this->addFlash('error', 'Cette location est deja annulee.');
            }
        }

        return $this->redirectToRoute('app_location_index', ['user_id' => $currentUser->getId()]);
    }

    #[Route('/{id}', name: 'app_location_delete', methods: ['POST'])]
    public function delete(Request $request, Location $location, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->userContext->requireCurrentUser();
        $this->assertOwnedByCurrentUser($location, $currentUser);

        if ($this->isCsrfTokenValid('delete_location_' . $location->getId(), (string) $request->request->get('_token'))) {
            $materiel = $location->getMateriel();

            $entityManager->remove($location);
            $entityManager->flush();

            $this->refreshMaterielState($materiel);

            $this->addFlash('success', 'Location supprimee avec succes.');
        }

        return $this->redirectToRoute('app_location_index', ['user_id' => $currentUser->getId()]);
    }

    private function assertOwnedByCurrentUser(Location $location, Utilisateur $currentUser): void
    {
        if ($location->getUtilisateur()?->getId() !== $currentUser->getId()) {
            throw $this->createNotFoundException();
        }
    }

    private function refreshMaterielState(?Materiel $materiel): void
    {
        if ($materiel instanceof Materiel) {
            $this->materielStateManager->refreshForMateriel($materiel);
        }
    }

    private function syncComputedAmount(Location $location): void
    {
        $dailyRate = (float) ($location->getMateriel()?->getPrixLocation() ?? 0.0);

        if ($dailyRate <= 0) {
            $location->setMontantTotal(0.0);

            return;
        }

        $start = $location->getDateDebut();
        $end = $location->getDateFin();

        if (!$start || !$end) {
            $location->setMontantTotal($dailyRate);

            return;
        }

        $dayCount = $this->calculateInclusiveDayCount($start, $end);

        if ($dayCount <= 0) {
            $location->setMontantTotal($dailyRate);

            return;
        }

        $location->setMontantTotal(round($dailyRate * $dayCount, 2));
    }

    private function calculateInclusiveDayCount(\DateTimeInterface $start, \DateTimeInterface $end): int
    {
        if ($end < $start) {
            return 0;
        }

        return ((int) $start->diff($end)->days) + 1;
    }

    private function applyLocationBusinessRules(
        FormInterface $form,
        Location $location,
        Utilisateur $currentUser,
        LocationRepository $locationRepository,
        bool $lockMateriel = false,
        ?Materiel $selectedMateriel = null,
        ?int $excludeLocationId = null,
    ): void {
        $materiel = $location->getMateriel();

        if (!$materiel instanceof Materiel) {
            return;
        }

        if ($materiel->getProprietaire()?->getId() === $currentUser->getId()) {
            $form->get('materiel')->addError(new FormError('Vous ne pouvez pas louer votre propre materiel.'));
        }

        if ($lockMateriel && $selectedMateriel instanceof Materiel && $selectedMateriel->getEtat() !== Materiel::ETAT_DISPONIBLE) {
            $form->get('materiel')->addError(new FormError('Ce materiel n\'est plus disponible. Retournez au marketplace.'));
        }

        $materielId = $materiel->getId();
        $dateDebut = $location->getDateDebut();
        $dateFin = $location->getDateFin();

        if ($materielId === null || !$dateDebut || !$dateFin) {
            return;
        }

        if ($locationRepository->hasOverlappingLocationForMateriel($materielId, $dateDebut, $dateFin, $excludeLocationId)) {
            $form->get('materiel')->addError(new FormError('Ce materiel est deja reserve sur la periode choisie. Merci de selectionner une autre date ou un autre materiel.'));
        }
    }

    /**
     * @param Location[] $locations
     */
    private function refreshMaterielStatesFromLocations(array $locations): void
    {
        $seen = [];

        foreach ($locations as $location) {
            $materiel = $location->getMateriel();
            $materielId = $materiel?->getId();

            if (!$materiel instanceof Materiel || $materielId === null || isset($seen[$materielId])) {
                continue;
            }

            $seen[$materielId] = true;
            $this->materielStateManager->refreshForMateriel($materiel);
        }
    }

    #[Route('/api/booked-periods/{materielId}', name: 'app_location_booked_periods', methods: ['GET'])]
    public function bookedPeriods(int $materielId, LocationRepository $locationRepository): Response
    {
        $this->userContext->requireCurrentUser();

        $periods = $locationRepository->getBookedPeriodsForMateriel($materielId);

        return $this->json([
            'materielId' => $materielId,
            'periods' => $periods,
        ]);
    }
}
