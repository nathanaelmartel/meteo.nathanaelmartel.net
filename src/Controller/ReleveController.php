<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Measure;

class ReleveController extends AbstractController
{
    /**
     * @Route("/releve", name="releve")
     */
    public function index(Request $request)
    {
        $measure = new Measure();
        if ($request->isMethod('GET')) {
            $measure->setStatedAt(new \DateTime());
            $measure->setType('panneau-solaire');
        }
        $form = $this->createForm('App\Form\ReleveType', $measure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $lastMesure = $em->getRepository('App:Measure')->findLastReleveOfType($measure->getType(), $measure->getStatedAt());
            $measure_total = $measure->getReleve() - $lastMesure->getReleve();
            $day = new \DateTime($lastMesure->getMeasuredAt()->format('Y-m-d 00:00:00'));
            $day->modify('+1 day');
            $nb_days = $lastMesure->getMeasuredAt()->diff($measure->getStatedAt());
            $nb_days = $nb_days->format('%a');
            $measure_value = $measure_total / $nb_days;

            while ($day < $measure->getStatedAt()) {
                $measure_intermedaire = new Measure();
                $measure_intermedaire->setValue($measure_value);
                $measure_intermedaire->setType($measure->getType());
                $measure_intermedaire->setMeasuredAt($day);
                $measure_intermedaire->getStatedAt($measure->getStatedAt());
                $em->persist($measure_intermedaire);
                $em->flush();

                $day->modify('+1 day');
            }

            $measure->setValue($measure_value);
            $measure->setMeasuredAt($measure->getStatedAt());

            $em->persist($measure);
            $em->flush();

            $this->addFlash('success', 'Mesure enregistré');

            return $this->redirectToRoute('home');
        }

        return $this->render('releve/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
