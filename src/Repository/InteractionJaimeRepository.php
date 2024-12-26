<?php

namespace App\Repository;

use App\Entity\InteractionJaime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InteractionJaime>
 */
class InteractionJaimeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InteractionJaime::class);
    }

    //    /**
    //     * @return InteractionJaime[] Returns an array of InteractionJaime objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?InteractionJaime
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

// src/Repository/InteractionJaimeRepository.php

public function findByUserAndLivre($utilisateur, $livre): ?InteractionJaime
{
    return $this->createQueryBuilder('i')
        ->where('i.utilisateur = :utilisateur')
        ->andWhere('i.livre = :livre')
        ->setParameter('utilisateur', $utilisateur)
        ->setParameter('livre', $livre)
        ->getQuery()
        ->getOneOrNullResult();
}


}
