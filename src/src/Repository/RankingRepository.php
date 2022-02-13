<?php

namespace App\Repository;

use App\Entity\Ranking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Ranking|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ranking|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ranking[]    findAll()
 * @method Ranking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RankingRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ranking::class);
    }
}
