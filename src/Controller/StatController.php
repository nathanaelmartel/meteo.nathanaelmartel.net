<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class StatController extends AbstractController
{
    /**
     * @Route("/admin/stat/order/daily", name="admin_order_stat_day_site")
     */
    public function daily(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $day_end = new \DateTime(date('Y-m-d 23:59'));
        $day_start = new \DateTime(date('Y-m-d 00:00', strtotime('-10 days')));

        $stats = $em->getRepository('App:Measure')->getStat($day_end, $day_start, 10);

        return $this->render('stat/day.html.twig', [
            'day_end' => $day_end,
            'day_start' => $day_start,
            'stats' => $stats,
            'pagination' => [],
            'current_year' => '',
            'route' => 'admin_order_stat_day_site',
            'graph' => '',
        ]);
    }

    /**
     * @Route("/admin/stat/order/hourly", name="admin_order_stat_hour_site")
     */
    public function hourly(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $day_end = new \DateTime(date('Y-m-d 23:59'));
        $day_start = new \DateTime(date('Y-m-d 00:00'));

        $stats = $em->getRepository('App:Measure')->getStat($day_end, $day_start, 13);

        return $this->render('stat/day.html.twig', [
                'day_end' => $day_end,
                'day_start' => $day_start,
                'stats' => $stats,
                'pagination' => [],
                'current_year' => '',
                'route' => 'admin_order_stat_day_site',
                'graph' => '',
            ]);
    }

    /**
     * @Route("/admin/stat/order/monthly", name="admin_order_stat_month_site")
     */
    public function monthly(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $date = $request->query->get('date', '');
        if ('' == $date) {
            $day_end = new \DateTime(date('Y-12-31 23:59'));
            $day_start = new \DateTime(date('Y-01-01 00:00'));
        } else {
            $date = new \DateTime($date);
            $day_end = new \DateTime($date->format('Y-12-31 23:59'));
            $day_start = new \DateTime($date->format('Y-01-01 00:00'));
        }

        $stats = $em->getRepository('App:Measure')->getStat($day_end, $day_start, 7);
        //$years = $em->getRepository('App:Measure')->getYears($measure);

        $graph = ''; /*
        $graph = $em->getRepository('App:Measure')->getCaYear($measure, $day_end->format('Y'));
        $graph = '['.$day_end->format('Y').', '.implode(', ', $graph).'],';
        $last_year = $day_end->format('Y') - 1;
        $graph2 = $em->getRepository('App:Measure')->getCaYear($measure, $last_year);
        $graph2 = '['.$last_year.', '.implode(', ', $graph2).']';
        $graph .= $graph2;*/

        return $this->render('stat/day.html.twig', [
                'day_end' => $day_end,
                'day_start' => $day_start,
                'stats' => $stats,
                'pagination' => [],
                'current_year' => $day_end->format('Y'),
                'route' => 'admin_order_stat_month_site',
                'graph' => $graph,
            ]);
    }

    /**
     * @Route("/admin/stat/order/yearly/", name="admin_order_stat_year_site")
     */
    public function yearly()
    {
        $em = $this->getDoctrine()->getManager();
        $day_end = new \DateTime(date('Y-12-31 23:59'));
        $day_start = new \DateTime(date('Y-01-01 00:00', strtotime('-10 years')));

        $stats = $em->getRepository('App:Measure')->getStat($day_end, $day_start, 4);

        $graph = ''; /*
        foreach ($years as $year) {
            $data = $em->getRepository('App:Measure')->getCaYear($measure, $year['day']);
            $graph .= '['.$year['day'].', '.implode(', ', $data).'],';
        }
        $graph = trim($graph, ',');*/

        return $this->render('stat/day.html.twig', [
                'day_end' => $day_end,
                'day_start' => $day_start,
                'stats' => $stats,
                'pagination' => array(),
                'current_year' => '',
                'route' => 'admin_order_stat_month_site',
                'graph' => $graph,
            ]);
    }
}
