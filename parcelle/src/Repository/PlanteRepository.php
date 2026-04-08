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

    public function findByCriteria(?string $search = null, string $sort = 'idPlante', string $direction = 'ASC'): array 
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.parcelle', 'par');
        
        $allowedSorts = ['idPlante' => 'p.idPlante', 'nom' => 'p.nom', 'type' => 'p.type', 'quantite' => 'p.quantite', 'surface' => 'p.surface', 'parcelle' => 'par.localisation'];
        $sortField = $allowedSorts[$sort] ?? 'p.idPlante';
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $qb->orderBy($sortField, $direction);

        if ($search) {
            $qb->andWhere('p.idPlante LIKE :search OR p.nom LIKE :search OR p.type LIKE :search OR CONCAT(p.quantite, \'\') LIKE :search OR CONCAT(p.surface, \'\') LIKE :search OR par.localisation LIKE :search OR par.proprietaire LIKE :search OR par.typeDeSol LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function getStats(): array
    {
        $total = $this->createQueryBuilder('p')
            ->select('COUNT(p.idPlante)')
            ->getQuery()->getSingleScalarResult();

        $totalSurface = $this->createQueryBuilder('p')
            ->select('SUM(p.surface)')
            ->getQuery()->getSingleScalarResult() ?: 0;

        $avgSurface = $total > 0 ? $totalSurface / $total : 0;

        $types = $this->createQueryBuilder('p')
            ->select('p.type, COUNT(p.idPlante) as count')
            ->groupBy('p.type')
            ->getQuery()->getResult();

        return [
            'total' => (int) $total,
            'totalSurface' => round((float) $totalSurface, 2),
            'avgSurface' => round((float) $avgSurface, 2),
            'types' => $types,
        ];
    }
}
?>

