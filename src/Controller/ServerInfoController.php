<?php

namespace App\Controller;

use App\SimplementWeb\SettingsBundle\Service\Setting;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ServerInfoController extends AbstractController
{
    /**
     * @Route("/admin/tools/server-info", name="admin_tools_serverinfo")
     */
    public function serverInfo(Setting $setting)
    {
        $info = [
            'phpversion' => phpversion(),
            'env' => $this->getParameter('kernel.environment'),
        ];

        return $this->render('server/info.html.twig', [
            'info' => $info,
        ]);
    }
}
