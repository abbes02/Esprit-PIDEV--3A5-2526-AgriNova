<?php

namespace App\Repository;

use App\Entity\Location;
use App\Entity\Materiel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Materiel>
 */
class MaterielRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Materiel::class);
    }

    /**
     * @return Materiel[]
     */
    public function findOwnedByUser(int $userId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.proprietaire = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('m.dateAjout', 'DESC')
            ->addOrderBy('m.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Materiel[]
     */
    public function findMarketplaceItems(): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.pannes', 'p', 'WITH', 'p.dateReparation IS NULL')
            ->addSelect('p')
            ->orderBy('m.dateAjout', 'DESC')
            ->addOrderBy('m.id', 'DESC')
            ->addOrderBy('p.datePanne', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Materiel[]
     */
    public function findAvailableForLocation(?int $includeMaterielId = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->andWhere('m.etat = :availableState')
            ->setParameter('availableState', Materiel::ETAT_DISPONIBLE)
            ->orderBy('m.nom', 'ASC')
            ->addOrderBy('m.id', 'ASC');

        if ($includeMaterielId !== null && $includeMaterielId > 0) {
            $qb
                ->orWhere('m.id = :includeMaterielId')
                ->setParameter('includeMaterielId', $includeMaterielId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Materiel[]
     */
    public function findReportableForUser(int $userId): array
    {
        $today = new \DateTimeImmutable('today');

        return $this->createQueryBuilder('m')
            ->leftJoin('m.proprietaire', 'owner')
            ->leftJoin('m.locations', 'l')
            ->leftJoin('l.utilisateur', 'renter')
            ->andWhere('owner.idUtilisateur = :userId OR (renter.idUtilisateur = :userId AND l.dateDebut <= :today AND l.dateFin >= :today AND (l.statut IS NULL OR l.statut = :enCours))')
            ->setParameter('userId', $userId)
            ->setParameter('today', $today)
            ->setParameter('enCours', Location::STATUT_EN_COURS)
            ->groupBy('m.id')
            ->orderBy('m.nom', 'ASC')
            ->addOrderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function isReportableByUser(int $materielId, int $userId): bool
    {
        $today = new \DateTimeImmutable('today');

        $count = (int) $this->createQueryBuilder('m')
            ->select('COUNT(DISTINCT m.id)')
            ->leftJoin('m.proprietaire', 'owner')
            ->leftJoin('m.locations', 'l')
            ->leftJoin('l.utilisateur', 'renter')
            ->andWhere('m.id = :materielId')
            ->andWhere('owner.idUtilisateur = :userId OR (renter.idUtilisateur = :userId AND l.dateDebut <= :today AND l.dateFin >= :today AND (l.statut IS NULL OR l.statut = :enCours))')
            ->setParameter('materielId', $materielId)
            ->setParameter('userId', $userId)
            ->setParameter('today', $today)
            ->setParameter('enCours', Location::STATUT_EN_COURS)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function isOwnedByUser(int $materielId, int $userId): bool
    {
        $count = (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.id = :id')
            ->andWhere('m.proprietaire = :userId')
            ->setParameter('id', $materielId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function hasLinkedRows(int $materielId): bool
    {
        $sql = <<<'SQL'
SELECT
    (
        (SELECT COUNT(*) FROM location_web WHERE materiel_id = :materielId)
        +
        (SELECT COUNT(*) FROM panne_web WHERE materiel_id = :materielId)
    ) AS linked_count
SQL;

        $linkedCount = (int) $this->getEntityManager()
            ->getConnection()
            ->fetchOne($sql, ['materielId' => $materielId]);

        return $linkedCount > 0;
    }
}
