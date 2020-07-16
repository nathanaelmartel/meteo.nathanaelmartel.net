<?php

namespace App\Controller;

use App\Entity\Measure;
use App\SimplementWeb\SettingsBundle\Service\Setting;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/{measure}/{value}", name="api_measure")
     */
    public function measure(string $measure, string $value, Setting $setting)
    {
        $Measure = $this->addMeasure($measure, $value);
        $setting->set(sprintf('last_%s', $measure), date('Y-m-d H:i:s'));

        return new Response('ok', 200);
    }

    public function addMeasure($type, $value)
    {
        $em = $this->getDoctrine()->getManager();

        $measure = new Measure();
        $measure->setType($type);
        $measure->setValue($value);
        $measure->setMeasuredAt(new \DateTime('now'));

        $em->persist($measure);
        $em->flush();

        return $measure;
    }
}
