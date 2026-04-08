<?php

namespace App\Repository;

use App\Entity\Parcelle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Parcelle>
 */
class ParcelleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parcelle::class);
    }

    public function findByCriteria(?string $search = null, string $sort = 'idParcelle', string $direction = 'ASC'): array
    {
        $qb = $this->createQueryBuilder('p');

        $allowedSorts = [
            'idParcelle' => 'p.idParcelle',
            'proprietaire' => 'p.proprietaire',
            'localisation' => 'p.localisation',
            'longueur' => 'p.longueur',
            'largeur' => 'p.largeur',
            'typeDeSol' => 'p.typeDeSol',
        ];
        $sortField = $allowedSorts[$sort] ?? 'p.idParcelle';
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $qb->orderBy($sortField, $direction);

        if ($search) {
            $qb->andWhere('(p.idParcelle LIKE :search OR p.proprietaire LIKE :search OR p.localisation LIKE :search OR p.typeDeSol LIKE :search OR p.longueur LIKE :search OR p.largeur LIKE :search)')
                ->setParameter('search', '%' . $search . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function findByUserCriteria(int $userId, ?string $search = null, string $sort = 'idParcelle', string $direction = 'ASC'): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('IDENTITY(p.utilisateur) = :userId')
            ->setParameter('userId', $userId);

        $allowedSorts = [
            'idParcelle' => 'p.idParcelle',
            'proprietaire' => 'p.proprietaire',
            'localisation' => 'p.localisation',
            'longueur' => 'p.longueur',
            'largeur' => 'p.largeur',
            'typeDeSol' => 'p.typeDeSol',
        ];
        $sortField = $allowedSorts[$sort] ?? 'p.idParcelle';
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $qb->orderBy($sortField, $direction);

        if ($search) {
            $qb->andWhere('(p.idParcelle LIKE :search OR p.proprietaire LIKE :search OR p.localisation LIKE :search OR p.typeDeSol LIKE :search OR p.longueur LIKE :search OR p.largeur LIKE :search)')
                ->setParameter('search', '%' . $search . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function getStats(): array
    {
        $total = $this->createQueryBuilder('p')
            ->select('COUNT(p.idParcelle)')
            ->getQuery()->getSingleScalarResult();

        $totalSurface = $this->createQueryBuilder('p')
            ->select('SUM(p.longueur * p.largeur)')
            ->getQuery()->getSingleScalarResult() ?: 0;

        $avgSurface = $total > 0 ? $totalSurface / $total : 0;

        $types = $this->createQueryBuilder('p')
            ->select('p.typeDeSol, COUNT(p.idParcelle) as count')
            ->groupBy('p.typeDeSol')
            ->getQuery()->getResult();

        return [
            'total' => (int) $total,
            'totalSurface' => round((float) $totalSurface, 2),
            'avgSurface' => round((float) $avgSurface, 2),
            'types' => $types,
        ];
    }

    public function getStatsByUser(int $userId): array
    {
        $total = $this->createQueryBuilder('p')
            ->select('COUNT(p.idParcelle)')
            ->andWhere('IDENTITY(p.utilisateur) = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()->getSingleScalarResult();

        $totalSurface = $this->createQueryBuilder('p')
            ->select('SUM(p.longueur * p.largeur)')
            ->andWhere('IDENTITY(p.utilisateur) = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()->getSingleScalarResult() ?: 0;

        $avgSurface = $total > 0 ? $totalSurface / $total : 0;

        $types = $this->createQueryBuilder('p')
            ->select('p.typeDeSol, COUNT(p.idParcelle) as count')
            ->andWhere('IDENTITY(p.utilisateur) = :userId')
            ->setParameter('userId', $userId)
            ->groupBy('p.typeDeSol')
            ->getQuery()->getResult();

        return [
            'total' => (int) $total,
            'totalSurface' => round((float) $totalSurface, 2),
            'avgSurface' => round((float) $avgSurface, 2),
            'types' => $types,
        ];
    }

    public function findOwnedById(int $idParcelle, int $userId): ?Parcelle
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.idParcelle = :idParcelle')
            ->andWhere('IDENTITY(p.utilisateur) = :userId')
            ->setParameter('idParcelle', $idParcelle)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
