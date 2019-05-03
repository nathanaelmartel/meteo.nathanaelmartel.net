<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Measure;

class ApiController extends Controller
{
    /**
     * @Route("/", name="default")
     */
    public function default()
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }

    /**
     * @Route("/api", name="api")
     */
    public function index()
    {
        return $this->render('api/index.html.twig', [
                'controller_name' => 'ApiController',
            ]);
    }

    /**
     * @Route("/api/temperature/{value}", name="api_temperature")
     */
    public function temperature($value)
    {
        $measure = $this->addMeasure('temperature', $value);

        return new Response('ok', 200);
    }

    /**
     * @Route("/api/humidity/{value}", name="api_humidity")
     */
    public function humidity($value)
    {
        $measure = $this->addMeasure('humidity', $value);

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
