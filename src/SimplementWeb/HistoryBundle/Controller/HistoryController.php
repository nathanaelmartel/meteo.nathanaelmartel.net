<?php

namespace App\SimplementWeb\HistoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\SimplementWeb\HistoryBundle\Entity\History;

/**
 * History controller.
 *
 * @Route("admin/tools/history")
 */
class HistoryController extends AbstractController
{
    /**
     * Lists all history entities.
     *
     * @Route("/", name="tools_history_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $histories = $em->getRepository('History:History')->findBy([], ['updated' => 'DESC'], 100);

        return $this->render('@SimplementWebHistory/index.html.twig', array(
            'histories' => $histories,
        ));
    }

    /**
     * Finds and displays a history entity.
     *
     * @Route("/{id}", name="tools_history_show")
     */
    public function showAction(History $history)
    {
        return $this->render('@SimplementWebHistory/show.html.twig', array(
            'history' => $history,
        ));
    }
}
