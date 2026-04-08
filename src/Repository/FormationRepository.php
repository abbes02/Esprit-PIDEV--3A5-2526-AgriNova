<?php

namespace App\Repository;

use App\Entity\Formation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formation::class);
    }

    /** @return Formation[] */
    public function search(string $q): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.titre LIKE :q OR f.domaine LIKE :q OR f.niveau LIKE :q OR f.statut LIKE :q')
            ->setParameter('q', '%' . $q . '%')
            ->orderBy('f.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
