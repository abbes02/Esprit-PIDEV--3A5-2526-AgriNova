<?php

namespace App\Repository;

use App\Entity\Plante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Plante>
 */
class PlanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plante::class);
    }

    public function findByUserCriteria(
        int $userId,
        ?string $search = null,
        string $sort = 'idPlante',
        string $direction = 'ASC'
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.parcelle', 'par')
            ->andWhere('IDENTITY(par.utilisateur) = :userId')
            ->setParameter('userId', $userId);

        $allowedSorts = [
            'idPlante' => 'p.idPlante',
            'nom' => 'p.nom',
            'type' => 'p.type',
            'quantite' => 'p.quantite',
            'surface' => 'p.surface',
            'parcelle' => 'par.localisation',
        ];

        $sortField = $allowedSorts[$sort] ?? 'p.idPlante';
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $qb->orderBy($sortField, $direction);

        if ($search) {
            $qb
                ->andWhere(
                    'p.idPlante LIKE :search
                    OR p.nom LIKE :search
                    OR p.type LIKE :search
                    OR CONCAT(p.quantite, \'\') LIKE :search
                    OR CONCAT(p.surface, \'\') LIKE :search
                    OR par.localisation LIKE :search
                    OR par.proprietaire LIKE :search
                    OR par.typeDeSol LIKE :search'
                )
                ->setParameter('search', '%' . $search . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function getStatsByUser(int $userId): array
    {
        $baseQuery = $this->createQueryBuilder('p')
            ->innerJoin('p.parcelle', 'par')
            ->andWhere('IDENTITY(par.utilisateur) = :userId')
            ->setParameter('userId', $userId);

        $total = (clone $baseQuery)
            ->select('COUNT(p.idPlante)')
            ->getQuery()
            ->getSingleScalarResult();

        $totalSurface = (clone $baseQuery)
            ->select('SUM(p.surface)')
            ->getQuery()
            ->getSingleScalarResult() ?: 0;

        $avgSurface = $total > 0 ? $totalSurface / $total : 0;

        $types = (clone $baseQuery)
            ->select('p.type, COUNT(p.idPlante) as count')
            ->groupBy('p.type')
            ->getQuery()
            ->getResult();

        return [
            'total' => (int) $total,
            'totalSurface' => round((float) $totalSurface, 2),
            'avgSurface' => round((float) $avgSurface, 2),
            'types' => $types,
        ];
    }

    public function findOwnedById(int $idPlante, int $userId): ?Plante
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.parcelle', 'par')
            ->andWhere('p.idPlante = :idPlante')
            ->andWhere('IDENTITY(par.utilisateur) = :userId')
            ->setParameter('idPlante', $idPlante)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
