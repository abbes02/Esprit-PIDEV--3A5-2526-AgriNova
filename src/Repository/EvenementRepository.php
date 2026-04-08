<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /** @return Evenement[] */
    public function search(string $q): array
    {
        return $this->createQueryBuilder('e')
            ->join('e.formation', 'f')
            ->where('e.lieu LIKE :q OR e.type LIKE :q OR e.statut LIKE :q OR f.titre LIKE :q')
            ->setParameter('q', '%' . $q . '%')
            ->orderBy('e.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
