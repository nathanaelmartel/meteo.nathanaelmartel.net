<?php

namespace App\Controller;

use App\Entity\Measure;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReleveController extends AbstractController
{
    /**
     * @Route("/releve/{type}", name="releve_type")
     */
    public function type(Request $request, string $type)
    {
        $measure = new Measure();
        $measure->setType($type);
        if ($request->isMethod('GET')) {
            $measure->setStatedAt(new \DateTime());
        }
        $form = $this->createForm('App\Form\ReleveType', $measure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ('voiture' == $measure->getType()) {
                $measure->setValue($form['kilometre']->getData());
                $measure->setMeasuredAt(new \DateTime('now'));
                $measure->setData($form['essence']->getData());
            } else {
                $lastMesure = $em->getRepository(Measure::class)->findLastReleveOfType($measure->getType(), $measure->getStatedAt());
                $measure_total = $measure->getReleve() - $lastMesure->getReleve();
                $day = new \DateTime($lastMesure->getMeasuredAt()->format('Y-m-d 00:00:00'));
                $day->modify('+1 day');
                $nb_days = $lastMesure->getMeasuredAt()->diff($measure->getStatedAt());
                $nb_days = $nb_days->format('%a');
                $measure_value = $measure_total / $nb_days;

                $lastMesure->setValue($measure_value);
                $em->persist($lastMesure);
                $em->flush();

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
                $measure->setValue(0);
                $measure->setMeasuredAt($measure->getStatedAt());
            }

            $em->persist($measure);
            $em->flush();

            $this->addFlash('success', 'Relève enregistré');

            if ('voiture' != $measure->getType()) {
                $this->addFlash('info', sprintf(
                    '%s %s depuis la dernière relève, soit %s %s / jour.',
                    $measure_total,
                    $measure->getUnit(),
                    number_format($measure_value, 3, ',', ' '),
                    $measure->getUnit()
                ));
            }

            return $this->redirectToRoute('last_month');
        }

        return $this->render('releve/releve.html.twig', [
            'form' => $form->createView(),
            'measure' => $measure,
        ]);
    }

    /**
     * @Route("/releve", name="releve")
     */
    public function index(Request $request)
    {
        return $this->render('releve/index.html.twig', [
            'types' => Measure::TYPES,
            'icons' => Measure::TYPESICONS,
        ]);
    }
}
