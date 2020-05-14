<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\SimplementWeb\SettingsBundle\Service\Setting;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Setting $setting)
    {
        $em = $this->getDoctrine()->getManager();

        foreach (['temperature', 'humidity', 'pressure'] as $type) {
            $graphiques[$type] = $em->getRepository('App:Measure')->getHourlyStats($type, new \DateTime('-1 day'));
        }

        return $this->render('default/index.html.twig', [
            'last_temperature' => new \DateTime($setting->get('last_temperature')),
            'daily_stats' => $em->getRepository('App:Measure')->getStats('temperature', new \DateTime('-1 day')),
            'hourly_stats' => $em->getRepository('App:Measure')->getStats('temperature', new \DateTime('-1 hour')),
            'graphiques' => $graphiques,
            'breadcrumb' => 'Dernières 24h',
        ]);
    }

    /**
     * @Route("/30-derniers-jours", name="last_month")
     */
    public function lastMonth(Setting $setting)
    {
        $em = $this->getDoctrine()->getManager();

        foreach (['temperature', 'humidity', 'pressure'] as $type) {
            $graphiques[$type] = $em->getRepository('App:Measure')->getDailyStats($type, new \DateTime('-30 days'));
        }

        return $this->render('default/index.html.twig', [
            'last_temperature' => new \DateTime($setting->get('last_temperature')),
            'daily_stats' => $em->getRepository('App:Measure')->getStats('temperature', new \DateTime('-30 day')),
            'hourly_stats' => $em->getRepository('App:Measure')->getStats('temperature', new \DateTime('-1 day')),
            'graphiques' => $graphiques,
            'breadcrumb' => '30 derniers jours',
        ]);
    }
}
