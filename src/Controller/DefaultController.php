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

        return $this->render('default/index.html.twig', [
            'last_temperature' => new \DateTime($setting->get('last_temperature')),
            'daily_stats' => $em->getRepository('App:Measure')->getStats('temperature', new \DateTime('-1 day')),
            'hourly_stats' => $em->getRepository('App:Measure')->getStats('temperature', new \DateTime('-1 hour')),
            'graph' => $em->getRepository('App:Measure')->getHourlyStats('temperature', new \DateTime('-1 day')),
        ]);
    }
}
