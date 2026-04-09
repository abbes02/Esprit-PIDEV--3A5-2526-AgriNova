<?php

namespace App\Service;

use App\Entity\Location;
use Doctrine\ORM\EntityManagerInterface;

class LocationStatusManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function applyAutomaticStatus(Location $location): bool
    {
        if ($location->getStatut() === Location::STATUT_ANNULEE) {
            return false;
        }

        $dateFin = $location->getDateFin();
        if ($dateFin === null) {
            return false;
        }

        $today = new \DateTimeImmutable('today');
        $newStatus = $dateFin < $today
            ? Location::STATUT_TERMINEE
            : Location::STATUT_EN_COURS;

        if ($location->getStatut() === $newStatus) {
            return false;
        }

        $location->setStatut($newStatus);
        $this->entityManager->persist($location);

        return true;
    }

    /**
     * @param iterable<Location> $locations
     */
    public function applyAutomaticStatusForMany(iterable $locations): bool
    {
        $changed = false;

        foreach ($locations as $location) {
            $changed = $this->applyAutomaticStatus($location) || $changed;
        }

        if ($changed) {
            $this->entityManager->flush();
        }

        return $changed;
    }

    public function cancel(Location $location): bool
    {
        if ($location->getStatut() === Location::STATUT_ANNULEE) {
            return false;
        }

        $location->setStatut(Location::STATUT_ANNULEE);
        $this->entityManager->persist($location);
        $this->entityManager->flush();

        return true;
    }
}
