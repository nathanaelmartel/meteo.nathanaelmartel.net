<?php

namespace App\Repository;

use App\Entity\Measure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @method Measure|null find($id, $lockMode = null, $lockVersion = null)
 * @method Measure|null findOneBy(array $criteria, array $orderBy = null)
 * @method Measure[]    findAll()
 * @method Measure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeasureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Measure::class);
    }

    public function getStats(string $type, \DateTime $from, DateTime $to = null)
    {
        if (is_null($to)) {
            $to = new \DateTime();
        }

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addScalarResult('avg', 'avg');
        $rsm->addScalarResult('min', 'min');
        $rsm->addScalarResult('max', 'max');

        $query = $this->getEntityManager()
            ->createNativeQuery('
                SELECT avg(value) AS avg, min(value) AS min, max(value) AS max
                FROM `measure`
                WHERE type=:type AND measured_at BETWEEN :from AND :to
                ', $rsm);
        $results = $query->setParameter(':from', $from->format('Y-m-d H:i:s'))
            ->setParameter(':to', $to->format('Y-m-d H:i:s'))
            ->setParameter(':type', $type)
            ->getResult();

        return $results[0];
    }

    // /**
    //  * @return Measure[] Returns an array of Measure objects
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
    public function findOneBySomeField($value): ?Measure
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
