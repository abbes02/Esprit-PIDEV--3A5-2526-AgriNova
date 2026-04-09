<?php

namespace App\Service;

use App\Entity\Materiel;
use App\Repository\LocationRepository;
use App\Repository\PanneRepository;
use Doctrine\ORM\EntityManagerInterface;

class MaterielStateManager
{
    public function __construct(
        private readonly LocationRepository $locationRepository,
        private readonly PanneRepository $panneRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function refreshForMateriel(Materiel $materiel, bool $flush = true): void
    {
        $materielId = $materiel->getId();
        if ($materielId === null) {
            return;
        }

        $newEtat = Materiel::ETAT_DISPONIBLE;

        if ($this->panneRepository->hasUnresolvedForMateriel($materielId)) {
            $newEtat = Materiel::ETAT_EN_PANNE;
        } elseif ($this->locationRepository->hasCurrentLocationForMateriel($materielId)) {
            $newEtat = Materiel::ETAT_LOUE;
        }

        if ($materiel->getEtat() === $newEtat) {
            return;
        }

        $materiel->setEtat($newEtat);
        $this->entityManager->persist($materiel);

        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
