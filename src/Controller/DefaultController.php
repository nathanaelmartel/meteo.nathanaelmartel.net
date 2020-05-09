<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\SimplementWeb\SettingsBundle\Service\Setting;
use App\SimplementWeb\HistoryBundle\Service\History;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Setting $setting, History $history)
    {
        /*$setting->get('key');
        $setting->set('key', 'value');
        $setting->group('key');*/

        //$h = $history->add('Test', null, 'information', ['foo' => 'bar']);
        //$history->add('Beta', $h, 'information', ['foo' => 'bar']);

        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }
}
