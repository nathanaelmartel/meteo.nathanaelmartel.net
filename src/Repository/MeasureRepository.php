<?php

namespace App\Repository;

use App\Entity\Measure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @method Measure|null find($id, $lockMode = null, $lockVersion = null)
 * @method Measure|null findOneBy(array $criteria, array $orderBy = null)
 * @method Measure[]    findAll()
 * @method Measure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeasureRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Measure::class);
    }

    public function getYears($site)
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addScalarResult('day', 'day');
        $rsm->addScalarResult('min_date', 'min_date');
        $rsm->addScalarResult('max_date', 'max_date');

        $years = $this->getEntityManager()
                ->createNativeQuery('
                        SELECT
                        LEFT(MIN(`measured_at`), 10) as min_date,
                        LEFT(MAX(`measured_at`), 10) as max_date,
                        LEFT(measured_at, 4) as day
                        FROM `order`
                        WHERE site_id IN (:site_ids)
                        GROUP BY LEFT(measured_at, 4) ORDER BY LEFT(measured_at, 4) ASC', $rsm)
                ->setParameter(':site_ids', [$site->getId()])
                ->getResult();

        return $years;
    }

    public function getCaYear($site, $year)
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addScalarResult('ca', 'ca');
        $rsm->addScalarResult('month', 'month');

        $cas = $this->getEntityManager()
                    ->createNativeQuery('
                            SELECT sum(total_paid_tax_excl) as ca, LEFT(measured_at, 7) as month
                            FROM `order`
                            WHERE LEFT(measured_at, 4) =:year AND site_id IN (:site_ids)
                            GROUP BY LEFT(measured_at, 7) ORDER BY LEFT(measured_at, 7)', $rsm)
                    ->setParameter(':site_ids', [$site->getId()])
                    ->setParameter(':year', $year)
                    ->getResult();

        $ca = array(); /*
            for ($i = 1; $i < 13; ++$i) {
                $ca[sprintf('%02d', $i)] = 0;
            }*/

        foreach ($cas as $item) {
            $ca[substr($item['month'], -2)] = sprintf('%.00f', $item['ca']);
        }

        $has_data = false;
        for ($i = 12; $i > 0; --$i) {
            $month = sprintf('%02d', $i);
            if (isset($ca[$month]) && !$has_data) {
                $has_data = true;
            }
            if ($has_data && !isset($ca[$month])) {
                $ca[sprintf('%02d', $i)] = 0;
            }
        }

        ksort($ca);

        return $ca;
    }

    public function getStat($day_end, $day_start, $nbcar_date = 10, $date_field = 'measured_at')
    {
        $day_start_last_year = new \DateTime(date('Y-m-d 00:00', strtotime('-1 year', $day_start->getTimestamp())));

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addScalarResult('day', 'day');
        $rsm->addScalarResult('max', 'max');
        $rsm->addScalarResult('min', 'min');
        $rsm->addScalarResult('avg', 'avg');
        $rsm->addScalarResult('selected', 'selected');
        $rsm->addScalarResult('min_date', 'min_date');

        $current_year = $this->getEntityManager()
                ->createNativeQuery('
                        SELECT
                        MIN(`measured_at`) as min_date,
                        LEFT('.$date_field.', :nbcar_date) as day,
                        avg(value) as `avg`,
                        min(value) as min,
                        max(value) as max,
                        (min(`measured_at`) between :start and :end) as selected
                        FROM `measure`
                        WHERE `measured_at` between :start_last_year and :end AND type IN (:measure)
                        GROUP BY LEFT('.$date_field.', :nbcar_date)', $rsm)
                ->setParameter(':measure', 'temperature')
                ->setParameter(':nbcar_date', $nbcar_date)
                ->setParameter(':start_last_year', $day_start_last_year->format('Y-m-d H:00'))
                ->setParameter(':start', $day_start->format('Y-m-d H:00'))
                ->setParameter(':end', $day_end->format('Y-m-d H:59'))
                ->getResult();

        $stats = array();
        foreach ($current_year as $day) {
            $stats[$day['day']] = $day;
            $stats[$day['day']]['display_day'] = $day['day'];
            if (7 == $nbcar_date) {
                if ('week_add' == $date_field) {
                    $date = new \DateTime($day['min_date']);
                    $stats[$day['day']]['display_day'] = $date->format('d/m/Y');
                } else {
                    $date = new \DateTime($day['day']);
                    $stats[$day['day']]['display_day'] = $date->format('F Y');
                }
            }
            if (13 == $nbcar_date) {
                $date = new \DateTime($day['day'].':00');
                $stats[$day['day']]['display_day'] = $date->format('d/m/Y');
            }
            if (10 == $nbcar_date) {
                $date = new \DateTime($day['day']);
                $stats[$day['day']]['display_day'] = $date->format('d/m/Y');
            }
        }

        // remove last year comparaison for dayly
        if (10 == $nbcar_date) {
            foreach ($stats as $key => $value) {
                if (!$value['selected']) {
                    unset($stats[$key]);
                }
            }
        }

        foreach ($current_year as $day) {
            $year = (int) substr($day['day'], 0, 4);
            $theday = substr($day['day'], 5);
            $lastyear = $year - 1;
            if ($nbcar_date > 4) {
                $lastyear_day = $lastyear.'-'.$theday;
            } else {
                $lastyear_day = $lastyear;
            }

            if (isset($stats[$lastyear_day])) {
                foreach ($stats[$lastyear_day] as $key => $value) {
                    if (in_array($key, array('avg', 'min', 'max'))) {
                        $stats[$day['day']]['lastyear_'.$key] = $value;
                        if (0 != $value) {
                            $stats[$day['day']]['lastyear_'.$key.'_percent'] = 100 * ($day[$key] - $value) / $value;
                        }
                    }
                }
            }
        }

        foreach ($stats as $key => $value) {
            if (!$value['selected']) {
                unset($stats[$key]);
            }
        }

        return $stats;
    }
}
