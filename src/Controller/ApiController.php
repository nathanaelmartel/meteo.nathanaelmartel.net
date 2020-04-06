<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Measure;

class ApiController extends BaseController
{
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
     * @Route("/api/{measure}/{value}", name="api_measure")
     */
    public function temperature(string $measure, string $value)
    {
        $Measure = $this->addMeasure($measure, $value);
        $this->setSetting(sprintf('last_%s', $measure), date('Y-m-d H:i:s'));

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
