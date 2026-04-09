<?php

namespace App\Repository;

use App\Entity\Location;
use App\Entity\Panne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Panne>
 */
class PanneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Panne::class);
    }

    /**
     * @return Panne[]
     */
    public function findByOwnerId(int $ownerId): array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('m')
            ->leftJoin('p.materiel', 'm')
            ->leftJoin('m.proprietaire', 'u')
            ->andWhere('u.idUtilisateur = :ownerId')
            ->setParameter('ownerId', $ownerId)
            ->orderBy('p.datePanne', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Panne[]
     */
    public function findAccessibleByUserId(int $userId): array
    {
        $today = new \DateTimeImmutable('today');

        return $this->createQueryBuilder('p')
            ->addSelect('m')
            ->leftJoin('p.materiel', 'm')
            ->leftJoin('m.proprietaire', 'owner')
            ->leftJoin('m.locations', 'l')
            ->leftJoin('l.utilisateur', 'renter')
            ->andWhere('owner.idUtilisateur = :userId OR (renter.idUtilisateur = :userId AND l.dateDebut <= :today AND l.dateFin >= :today AND (l.statut IS NULL OR l.statut = :enCours))')
            ->setParameter('userId', $userId)
            ->setParameter('today', $today)
            ->setParameter('enCours', Location::STATUT_EN_COURS)
            ->groupBy('p.id')
            ->orderBy('p.datePanne', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function isOwnedByUser(int $panneId, int $userId): bool
    {
        $count = (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->leftJoin('p.materiel', 'm')
            ->leftJoin('m.proprietaire', 'u')
            ->andWhere('p.id = :panneId')
            ->andWhere('u.idUtilisateur = :userId')
            ->setParameter('panneId', $panneId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function isAccessibleByUser(int $panneId, int $userId): bool
    {
        $today = new \DateTimeImmutable('today');

        $count = (int) $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.id)')
            ->leftJoin('p.materiel', 'm')
            ->leftJoin('m.proprietaire', 'owner')
            ->leftJoin('m.locations', 'l')
            ->leftJoin('l.utilisateur', 'renter')
            ->andWhere('p.id = :panneId')
            ->andWhere('owner.idUtilisateur = :userId OR (renter.idUtilisateur = :userId AND l.dateDebut <= :today AND l.dateFin >= :today AND (l.statut IS NULL OR l.statut = :enCours))')
            ->setParameter('panneId', $panneId)
            ->setParameter('userId', $userId)
            ->setParameter('today', $today)
            ->setParameter('enCours', Location::STATUT_EN_COURS)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function hasUnresolvedForMateriel(int $materielId): bool
    {
        $count = (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.materiel = :materielId')
            ->andWhere('p.dateReparation IS NULL')
            ->setParameter('materielId', $materielId)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function countUnresolvedByOwnerId(int $ownerId): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->leftJoin('p.materiel', 'm')
            ->leftJoin('m.proprietaire', 'u')
            ->andWhere('u.idUtilisateur = :ownerId')
            ->andWhere('p.dateReparation IS NULL')
            ->setParameter('ownerId', $ownerId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countUnresolvedByAccessibleUserId(int $userId): int
    {
        $today = new \DateTimeImmutable('today');

        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.id)')
            ->leftJoin('p.materiel', 'm')
            ->leftJoin('m.proprietaire', 'owner')
            ->leftJoin('m.locations', 'l')
            ->leftJoin('l.utilisateur', 'renter')
            ->andWhere('p.dateReparation IS NULL')
            ->andWhere('owner.idUtilisateur = :userId OR (renter.idUtilisateur = :userId AND l.dateDebut <= :today AND l.dateFin >= :today AND (l.statut IS NULL OR l.statut = :enCours))')
            ->setParameter('userId', $userId)
            ->setParameter('today', $today)
            ->setParameter('enCours', Location::STATUT_EN_COURS)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
