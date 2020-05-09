<?php

namespace App\SimplementWeb\SettingsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\SimplementWeb\SettingsBundle\Entity\Setting;

/**
 * Setting controller.
 *
 * @Route("admin/setting")
 */
class SettingController extends AbstractController
{
    /**
     * get setting for the key.
     *
     * @param string $key the setting name
     *
     * @return string/array
     */
    protected function getSetting($key, $default_value = null)
    {
        $em = $this->getDoctrine()->getManager();

        $setting = $em->getRepository('Settings:Setting')->findOneBy(array('name' => $key));

        if ($setting) {
            return $setting->getValue();
        }

        return $default_value;
    }

    public function getSettingsGrouped($key)
    {
        $em = $this->getDoctrine()->getManager();

        $settings = [];

        $results = $em->getRepository('Settings:Setting')->createQueryBuilder('s')
            ->andWhere('s.name LIKE :name')
            ->setParameter('name', $key.'%')
            ->getQuery()
            ->getResult();

        foreach ($results as $result) {
            $item_key = str_replace($key, '', $result->getName());
            $settings[trim($item_key, '_')] = $result->getValue();
        }

        return $settings;
    }

    /**
     * set setting for the key.
     *
     * @param string $key   the setting name
     * @param string $value the setting value
     */
    protected function setSetting($key, $value)
    {
        $em = $this->getDoctrine()->getManager();

        $setting = $em->getRepository('Settings:Setting')->findOneBy(array('name' => $key));

        if ($setting) {
            $setting->setValue($value);
            $em->persist($setting);
            $em->flush();

            return true;
        }

        if ('' != $value) {
            $setting = new Setting();
            $setting->setName($key);
            $setting->setValue($value);
            $em->persist($setting);
            $em->flush();
        }

        return false;
    }

    public function getSettings($settings)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($settings as $key => $setting) {
            if (isset($setting['default'])) {
                $settings[$key]['value'] = $this->getSetting($key, $setting['default']);
            } else {
                $settings[$key]['value'] = $this->getSetting($key);
            }
            if (!isset($setting['disabled'])) {
                $settings[$key]['disabled'] = false;
            }

            if (is_array($settings[$key]['value']) && !$settings[$key]['disabled']) {
                $value = '';
                foreach ($settings[$key]['value'] as $array_key => $array_value) {
                    if (is_array($array_value)) {
                        if (isset($array_value['name'])) {
                            $value .= "\n".$array_value['name']."\n";
                        }
                        if (isset($array_value['childs'])) {
                            foreach ($array_value['childs'] as $child) {
                                $value .= '- '.$child."\n";
                            }
                        }
                    } else {
                        $value .= $array_value."\n";
                    }
                }
                $settings[$key]['value'] = trim($value, "\n");
                //$settings[$key]['value'] = implode("\n", $settings[$key]['value']);
            }
            if (is_array($settings[$key]['value']) && $settings[$key]['disabled']) {
                $settings[$key]['value'] = var_export($settings[$key]['value'], true);
            }
        }

        return $settings;
    }

    public function saveSettings($settings, $request)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($settings as $key => $setting) {
            if (!$setting['disabled']) {
                $value = $request->request->get($key);
                if (isset($setting['data_type']) && ('array' == $setting['data_type'])) {
                    $value = explode("\r\n", $value);
                    foreach ($value as $array_key => $val) {
                        if ('' == $val) {
                            unset($value[$array_key]);
                        } else {
                            if ('- ' == substr($val, 0, 2) && isset($parent_key)) {
                                if (!isset($value[$parent_key]['childs'])) {
                                    $parent_name = $value[$parent_key];
                                    $value[$parent_key] = array(
                                        'name' => $parent_name,
                                        'childs' => array(),
                                    );
                                }
                                $value[$parent_key]['childs'][] = substr($val, 2);
                                unset($value[$array_key]);
                            } else {
                                $parent_key = $array_key;
                            }
                        }
                    }
                }
                $this->setSetting($key, $value);
            }
        }
    }

    /**
     * @Route("/mail", name="setting_mail")
     */
    public function mailLayout(Request $request)
    {
        $settings = array(
            'email_from' => array(
                'label' => 'Mail From',
                'type' => 'text',
            ),
            'email_from_name' => array(
                'label' => 'Name From',
                'type' => 'text',
            ),
            'email_bcc' => array(
                'label' => 'Mail BCC',
                'type' => 'text',
            ),
            'email_layout' => array(
                'label' => 'Mail layout',
                'type' => 'textarea',
            ),
        );

        $settings = $this->getSettings($settings);

        if ($request->isMethod('POST')) {
            $settings = $this->saveSettings($settings, $request);

            $this->addFlash('success', 'Paramètres enregistrés');

            return $this->redirectToRoute('setting_mail');
        }

        return $this->render('@SimplementWebSettings/index.html.twig', array(
            'settings' => $settings,
            'setting_title' => 'Email',
        ));
    }

    /**
     * @Route("/map", name="setting_map")
     */
    public function map(Request $request)
    {
        $settings = array(
            'mapbox_key' => array(
                'label' => 'MapBox public key',
                'type' => 'text',
                'help' => 'Clé publique à récupérer sur <a href="https://www.mapbox.com/" target="_blank">www.mapbox.com</a>',
            ),
            'mapbox_style' => array(
                'label' => 'MapBox style',
                'type' => 'select',
                'options' => array(
                    'mapbox.light' => 'Light',
                    'mapbox.dark' => 'Dark',
                    'mapbox.streets' => 'Streets',
                    'mapbox.outdoors' => 'Outdoors',
                    'mapbox.satellite' => 'Satellite',
                ),
                'help' => 'Style de carte visible sur <a href="https://www.mapbox.com/maps/" target="_blank">www.mapbox.com</a>',
            ),
        );

        $settings = $this->getSettings($settings);

        if ($request->isMethod('POST')) {
            $settings = $this->saveSettings($settings, $request);

            $this->addFlash('success', 'Paramètres sauvegardés');

            return $this->redirectToRoute('setting_map');
        }

        return $this->render('@SimplementWebSettings/index.html.twig', array(
            'settings' => $settings,
            'setting_title' => 'Open Street Map / MapBox',
        ));
    }

    /**
     * @Route("/dropbox", name="setting_dropbox")
     */
    public function dropboxAction(Request $request)
    {
        $settings = array(
            'dropbox_client_id' => array(
                'label' => 'Client id',
                'type' => 'text',
            ),
            'dropbox_client_secret' => array(
                'label' => 'Client secret',
                'type' => 'text',
            ),
            'dropbox_access_token' => array(
                'label' => 'Access token',
                'type' => 'text',
            ),
        );

        $settings = $this->getSettings($settings);

        if ($request->isMethod('POST')) {
            $settings = $this->saveSettings($settings, $request);

            $this->addFlash('success', 'Paramètres sauvegardés');

            return $this->redirectToRoute('setting_dropbox');
        }

        return $this->render('@SimplementWebSettings/index.html.twig', array(
            'settings' => $settings,
            'setting_title' => 'Dropbox',
        ));
    }
}
