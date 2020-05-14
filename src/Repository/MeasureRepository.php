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

        $labels = [
            'temperature' => 'Température °C',
            'humidity' => 'Humidité %',
            'pressure' => 'Pression Pa',
        ];
        $label = $type;
        if (isset($labels[$type])) {
            $label = $labels[$type];
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
            'avg' => [$label],
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

    public function findLastReleveOfType(string $type, \DateTime $before = null)
    {
        if (is_null($before)) {
            $before = new \DateTime();
        }

        $results = $this->createQueryBuilder('m')
            ->andWhere('m.type = :type')
            ->andWhere('m.measured_at < :before')
            ->andWhere('m.releve IS NOT NULL')
            ->setParameter('type', $type)
            ->setParameter('before', $before->format('Y-m-d H:i:s'))
            ->orderBy('m.measured_at', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;

        return $results[0];
    }
}
