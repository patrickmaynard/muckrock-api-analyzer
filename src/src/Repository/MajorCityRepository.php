<?php

namespace App\Repository;

use App\Entity\MajorCity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MajorCity|null find($id, $lockMode = null, $lockVersion = null)
 * @method MajorCity|null findOneBy(array $criteria, array $orderBy = null)
 * @method MajorCity[]    findAll()
 * @method MajorCity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MajorCityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MajorCity::class);
    }

    public function getNextUnfilled()
    {
        $now = new \DateTime();
        $today = $now->format('Y-m-d');
        return $this->createQueryBuilder('m')
            ->andWhere('m.lastUpdate < :today or m.lastUpdate IS NULL')
            ->setParameter('today',  $today)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getWorstResponseTimeCities()
    {
        return $this->createQueryBUilder('m')
            ->orderBy('m.averageResponseTime', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getWorstSuccessRateCities()
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.successRate', 'ASC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return MajorCity[] Returns an array of MajorCity objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MajorCity
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
