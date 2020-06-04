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

        return $this->getStatsBy($type, $from, $to, 13);
        $keys = array_flip($measures['period']);
        $datas = [
            'period' => [],
            'avg' => [$measures['avg'][0]],
            'min' => ['min'],
            'max' => ['max'],
        ];
        array_shift($measures['avg']);
        array_shift($measures['min']);
        array_shift($measures['max']);

        $hour = clone $from;
        while ($hour < $to) {
            $formated_hour = $hour->format('H').'h';
            if (isset($keys[$formated_hour])) {
                $datas['period'][] = $formated_hour;
                $datas['avg'][] = $measures['avg'][$keys[$formated_hour]];
                $datas['min'][] = $measures['min'][$keys[$formated_hour]];
                $datas['max'][] = $measures['max'][$keys[$formated_hour]];
            } else {
                $datas['period'][] = $formated_hour;
                $datas['avg'][] = null;
                $datas['min'][] = null;
                $datas['max'][] = null;
            }

            $hour->modify('+1 hour');
        }

        return $datas;
    }

    public function getDailyStats(string $type, \DateTime $from, \DateTime $to = null)
    {
        return $this->getStatsBy($type, $from, $to, 10);
    }

    public function getStatsBy(string $type, \DateTime $from, \DateTime $to = null, $period = 13)
    {
        if (is_null($to)) {
            $to = new \DateTime();
        }

        $labels = [
            'temperature' => 'Température °C',
            'humidity' => 'Humidité %',
            'pressure' => 'Pression Pa',
            'panneau-solaire' => 'Production banneau solaire KWh',
        ];
        $label = $type;
        if (isset($labels[$type])) {
            $label = $labels[$type];
        }

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addScalarResult('period', 'period');
        $rsm->addScalarResult('avg', 'avg');
        $rsm->addScalarResult('min', 'min');
        $rsm->addScalarResult('max', 'max');

        $query = $this->getEntityManager()
            ->createNativeQuery('
                SELECT LEFT(measured_at, :period) AS period, avg(value) AS avg, min(value) AS min, max(value) AS max
                FROM `measure`
                WHERE type=:type AND measured_at BETWEEN :from AND :to
                GROUP BY LEFT(measured_at, :period)
                ', $rsm);
        $results = $query->setParameter(':from', $from->format('Y-m-d H:i:s'))
            ->setParameter(':to', $to->format('Y-m-d H:i:s'))
            ->setParameter(':type', $type)
            ->setParameter(':period', $period)
            ->getResult();
        /*
                foreach ($results as $result) {
                    foreach ($result as $key => $value) {
                        if (('period' == $key) && (13 == $period)) {
                            $graph[$key][] = substr($value, -2).'h';
                        } elseif (('period' == $key) && (10 == $period)) {
                            $date = new \DateTime($value);
                            $graph[$key][] = $date->format('d/m');
                        } else {
                            $graph[$key][] = round($value, 2);
                        }
                    }
                }*/

        $datas = [
            'period' => [],
            'avg' => [$label],
            'min' => ['min'],
            'max' => ['max'],
        ];
        $time_period = clone $from;
        while ($time_period < $to) {
            $period_key = '';
            if (13 == $period) {
                $formated_hour = $time_period->format('H').'h';
                if (isset($results[0])) {
                    $period_key = substr($results[0]['period'], -2).'h';
                }
            } else {
                $formated_hour = $time_period->format('d/m');
                if (isset($results[0])) {
                    $date = new \DateTime($results[0]['period']);
                    $period_key = $date->format('d/m');
                }
            }
            if ($period_key == $formated_hour) {
                $datas['period'][] = $formated_hour;
                foreach ($results[0] as $key => $value) {
                    if ('period' != $key) {
                        $datas[$key][] = $value;
                    }
                }
                array_shift($results);
            } else {
                $datas['period'][] = $formated_hour;
                $datas['avg'][] = null;
                $datas['min'][] = null;
                $datas['max'][] = null;
            }

            if (13 == $period) {
                $time_period->modify('+1 hour');
            } else {
                $time_period->modify('+1 day');
            }
        }

        return $datas;
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
