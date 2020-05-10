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

    public function getHourlyStats(string $type, \DateTime $from, \DateTime $to = null)
    {
        if (is_null($to)) {
            $to = new \DateTime();
        }

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addScalarResult('hour', 'hour');
        $rsm->addScalarResult('avg', 'avg');
        $rsm->addScalarResult('min', 'min');
        $rsm->addScalarResult('max', 'max');

        $query = $this->getEntityManager()
            ->createNativeQuery('
                SELECT LEFT(measured_at, 13) AS hour, avg(value) AS avg, min(value) AS min, max(value) AS max
                FROM `measure`
                WHERE type=:type AND measured_at BETWEEN :from AND :to
                GROUP BY LEFT(measured_at, 13)
                ', $rsm);
        $results = $query->setParameter(':from', $from->format('Y-m-d H:i:s'))
            ->setParameter(':to', $to->format('Y-m-d H:i:s'))
            ->setParameter(':type', $type)
            ->getResult();

        $graph = [
            'hour' => [],
            'avg' => ['avg'],
            'min' => ['min'],
            'max' => ['max'],
        ];
        foreach ($results as $result) {
            foreach ($result as $key => $value) {
                if ('hour' == $key) {
                    $graph[$key][] = substr($value, -2).'h';
                } else {
                    $graph[$key][] = round($value, 2);
                }
            }
        }

        return $graph;
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
