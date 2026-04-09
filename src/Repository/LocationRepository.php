<?php

namespace App\Repository;

use App\Entity\Location;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Location>
 */
class LocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }

    /**
     * @return Location[]
     */
    public function findByUtilisateurId(int $userId): array
    {
        return $this->createQueryBuilder('l')
            ->addSelect('m')
            ->leftJoin('l.materiel', 'm')
            ->andWhere('l.utilisateur = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('l.dateDebut', 'DESC')
            ->addOrderBy('l.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function isOwnedByUser(int $locationId, int $userId): bool
    {
        $count = (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.id = :id')
            ->andWhere('l.utilisateur = :userId')
            ->setParameter('id', $locationId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function hasCurrentLocationForMateriel(int $materielId): bool
    {
        $today = new \DateTimeImmutable('today');

        $count = (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.materiel = :materielId')
            ->andWhere('l.dateDebut <= :today')
            ->andWhere('l.dateFin >= :today')
            ->andWhere('(l.statut IS NULL OR l.statut = :enCours)')
            ->setParameter('materielId', $materielId)
            ->setParameter('today', $today)
            ->setParameter('enCours', Location::STATUT_EN_COURS)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function hasOverlappingLocationForMateriel(
        int $materielId,
        \DateTimeInterface $dateDebut,
        \DateTimeInterface $dateFin,
        ?int $excludeLocationId = null,
    ): bool {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.materiel = :materielId')
            ->andWhere('l.dateDebut <= :dateFin')
            ->andWhere('l.dateFin >= :dateDebut')
            ->andWhere('(l.statut IS NULL OR l.statut <> :annulee)')
            ->setParameter('materielId', $materielId)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->setParameter('annulee', Location::STATUT_ANNULEE);

        if ($excludeLocationId !== null && $excludeLocationId > 0) {
            $qb
                ->andWhere('l.id <> :excludeLocationId')
                ->setParameter('excludeLocationId', $excludeLocationId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function countActiveByUtilisateurId(int $userId): int
    {
        $today = new \DateTimeImmutable('today');

        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.utilisateur = :userId')
            ->andWhere('l.dateDebut <= :today')
            ->andWhere('l.dateFin >= :today')
            ->andWhere('(l.statut IS NULL OR l.statut = :enCours)')
            ->setParameter('userId', $userId)
            ->setParameter('today', $today)
            ->setParameter('enCours', Location::STATUT_EN_COURS)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countActiveGlobal(): int
    {
        $today = new \DateTimeImmutable('today');

        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.dateDebut <= :today')
            ->andWhere('l.dateFin >= :today')
            ->andWhere('(l.statut IS NULL OR l.statut = :enCours)')
            ->setParameter('today', $today)
            ->setParameter('enCours', Location::STATUT_EN_COURS)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get future and current booked periods for a material.
     * Used to show unavailable dates in the rental form.
     *
     * @return array<int, array{start: string, end: string, user: string}>
     */
    public function getBookedPeriodsForMateriel(int $materielId, ?int $excludeLocationId = null): array
    {
        $today = new \DateTimeImmutable('today');

        $qb = $this->createQueryBuilder('l')
            ->select('l.dateDebut', 'l.dateFin', 'CONCAT(u.prenom, \' \', u.nom) AS userName')
            ->leftJoin('l.utilisateur', 'u')
            ->andWhere('l.materiel = :materielId')
            ->andWhere('l.dateFin >= :today')
            ->andWhere('(l.statut IS NULL OR l.statut <> :annulee)')
            ->setParameter('materielId', $materielId)
            ->setParameter('today', $today)
            ->setParameter('annulee', Location::STATUT_ANNULEE)
            ->orderBy('l.dateDebut', 'ASC');

        if ($excludeLocationId !== null && $excludeLocationId > 0) {
            $qb
                ->andWhere('l.id <> :excludeLocationId')
                ->setParameter('excludeLocationId', $excludeLocationId);
        }

        $results = $qb->getQuery()->getResult();

        $periods = [];
        foreach ($results as $row) {
            $periods[] = [
                'start' => $row['dateDebut']->format('Y-m-d'),
                'end' => $row['dateFin']->format('Y-m-d'),
                'user' => $row['userName'] ?? 'Utilisateur',
            ];
        }

        return $periods;
    }

    /**
     * Get all booked periods for multiple materials (for API endpoint).
     *
     * @param int[] $materielIds
     * @return array<int, array<int, array{start: string, end: string}>>
     */
    public function getBookedPeriodsForMateriels(array $materielIds): array
    {
        if (empty($materielIds)) {
            return [];
        }

        $today = new \DateTimeImmutable('today');

        $results = $this->createQueryBuilder('l')
            ->select('IDENTITY(l.materiel) AS materielId', 'l.dateDebut', 'l.dateFin')
            ->andWhere('l.materiel IN (:materielIds)')
            ->andWhere('l.dateFin >= :today')
            ->andWhere('(l.statut IS NULL OR l.statut <> :annulee)')
            ->setParameter('materielIds', $materielIds)
            ->setParameter('today', $today)
            ->setParameter('annulee', Location::STATUT_ANNULEE)
            ->orderBy('l.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();

        $periods = [];
        foreach ($results as $row) {
            $matId = (int) $row['materielId'];
            if (!isset($periods[$matId])) {
                $periods[$matId] = [];
            }
            $periods[$matId][] = [
                'start' => $row['dateDebut']->format('Y-m-d'),
                'end' => $row['dateFin']->format('Y-m-d'),
            ];
        }

        return $periods;
    }
}
