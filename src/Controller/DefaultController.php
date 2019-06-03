<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="default")
     */
    public function default()
    {
        $em = $this->getDoctrine()->getManager();
        $day_end = new \DateTime();
        $day_start = new \DateTime('-1 day');

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
}
