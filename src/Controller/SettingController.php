<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Setting;
use League\Flysystem\Filesystem;

class SettingController extends BaseController
{
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
            }
            /*
            if (isset($setting['data_type']) && ('array' == $setting['data_type']) && ('' != $settings[$key]['value'])) {
                $settings[$key]['value'] = implode("\n", json_decode($settings[$key]['value'], true));
            }*/
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
                    //$value = json_encode($value);
                }
                $this->setSetting($key, $value);
            }
        }
    }

    /**
     * @Route("/setting/mail/preview/{key}", name="setting_mail_preview")
     */
    public function mailPreview(Request $request, $key)
    {
        return new Response($this->wrapMail($this->getSetting($key), ''));
    }

    /**
     * @Route("/setting/mail/resetpassword", name="setting_mail_reset_password")
     */
    public function mailResetPassword(Request $request)
    {
        $settings = array(
            'mail_reset_subject' => array(
                'label' => 'Mail subject',
                'type' => 'text',
            ),
            'mail_reset_content' => array(
                'label' => 'Mail content',
                'type' => 'textarea',
            ),
        );

        $settings = $this->getSettings($settings);

        if ($request->isMethod('POST')) {
            $settings = $this->saveSettings($settings, $request);

            $this->addFlash('success', 'Paramètres enregistrés');

            return $this->redirectToRoute('setting_mail_reset_password');
        }

        return $this->render('setting/edit.html.twig', array(
            'settings' => $settings,
            'setting_title' => 'User reset password',
            'breadcrumb' => 'Email',
            'email_previews' => ['mail_reset_content'],
        ));
    }

    /**
     * @Route("/setting/mail/newuser", name="setting_mail_new_user")
     */
    public function mailNewUser(Request $request)
    {
        $settings = array(
            'mail_new_user_subject' => array(
                'label' => 'Mail subject',
                'type' => 'text',
            ),
            'mail_new_user_content' => array(
                'label' => 'Mail content',
                'type' => 'textarea',
            ),
        );

        $settings = $this->getSettings($settings);

        if ($request->isMethod('POST')) {
            $settings = $this->saveSettings($settings, $request);

            $this->addFlash('success', 'Paramètres enregistrés');

            return $this->redirectToRoute('setting_mail_new_user');
        }

        return $this->render('setting/edit.html.twig', array(
            'settings' => $settings,
            'setting_title' => 'New user',
            'breadcrumb' => 'Email',
            'email_previews' => ['mail_new_user_content'],
        ));
    }

    /**
     * @Route("/setting/mail/passwordforgotten", name="setting_password_forgotten_user")
     */
    public function mailPasswordRecovery(Request $request)
    {
        $settings = array(
            'mail_password_forgotten_subject' => array(
                'label' => 'Mail subject',
                'type' => 'text',
            ),
            'mail_password_forgotten_content' => array(
                'label' => 'Mail content',
                'type' => 'textarea',
            ),
        );

        $settings = $this->getSettings($settings);

        if ($request->isMethod('POST')) {
            $settings = $this->saveSettings($settings, $request);

            $this->addFlash('success', 'Paramètres enregistrés');

            return $this->redirectToRoute('setting_password_forgotten_user');
        }

        return $this->render('setting/edit.html.twig', array(
            'settings' => $settings,
            'setting_title' => 'Password forgotten',
            'breadcrumb' => 'Email',
            'email_previews' => ['mail_password_forgotten_content'],
        ));
    }

    /**
     * @Route("/setting/mail/layout", name="setting_mail_layout")
     */
    public function mailLayout(Request $request)
    {
        $settings = array(
            'email_from' => array(
                'label' => 'Mail From',
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

            return $this->redirectToRoute('setting_mail_layout');
        }

        return $this->render('setting/edit.html.twig', array(
            'settings' => $settings,
            'setting_title' => 'Layout',
            'breadcrumb' => 'Email',
        ));
    }

    /**
     * @Route("/setting/choices", name="setting_choices")
     */
    public function choices(Request $request)
    {
        $settings = array(
            'app_choices' => array(
                'label' => 'Applications',
                'data_type' => 'array',
                'type' => 'textarea',
            ),
            'source_choices' => array(
                'label' => 'Source',
                'data_type' => 'array',
                'type' => 'textarea',
            ),
        );

        $settings = $this->getSettings($settings);

        if ($request->isMethod('POST')) {
            $settings = $this->saveSettings($settings, $request);

            $this->addFlash('success', 'Paramètres enregistrés');

            return $this->redirectToRoute('setting_choices');
        }

        return $this->render('setting/edit.html.twig', array(
            'settings' => $settings,
            'setting_title' => 'Choices',
        ));
    }

    /**
     * @Route("/setting/ftp", name="setting_ftp")
     */
    public function ftp(Request $request)
    {
        $settings = array(
            'filezilla' => array(
                'label' => 'FileZilla export',
                'data_type' => 'array',
                'type' => 'textarea',
            ),
        );

        $settings = $this->getSettings($settings);

        if ($request->isMethod('POST')) {
            $value = $request->request->get('filezilla');
            $servers = array();
            if ('' != $value) {
                $xml = simplexml_load_string($value);
                $json = json_encode($xml);
                $value = json_decode($json, true);
                if (isset($value['Servers']) && isset($value['Servers']['Server'])) {
                    foreach ($value['Servers']['Server'] as $server) {
                        $servers[] = $server;
                    }
                }

                $settings = $this->setSetting('filezilla', $servers);

                $this->addFlash('success', 'Paramètres enregistrés');
            } else {
            }

            return $this->redirectToRoute('setting_ftp');
        }
        $em = $this->getDoctrine()->getManager();

        return $this->render('setting/edit.html.twig', array(
            'settings' => $settings,
            'setting_title' => 'Choices',
            'filezilla' => $this->getSetting('filezilla', array()),
            'sites' => $em->getRepository('App:Site')->findBy([], ['name' => 'ASC']),
        ));
    }

    /**
     * @Route("/setting/ftp/to/site/{server}", name="setting_ftp_to_site")
     */
    public function ftpToSite(Request $request, $server)
    {
        $em = $this->getDoctrine()->getManager();
        $site_id = $request->request->get('site');
        $servers = $this->getSetting('filezilla', array());
        if (!isset($servers[$server])) {
            return $this->redirectToRoute('setting_ftp');
        }
        /*var_dump($servers[$server]);
        $site = $em->getRepository('App:Site')->findOneBy(['id' => 1]);
        var_dump($site->getFtpInfo());*/
        $site = $em->getRepository('App:Site')->findOneBy(['id' => $site_id]);
        $ftp_config = [
            'type' => (1 == $servers[$server]['Protocol']) ? 'sftp' : 'ftp',
            'sftp' => (1 == $servers[$server]['Protocol']),
            'host' => $servers[$server]['Host'],
            'username' => $servers[$server]['User'],
            'password' => base64_decode($servers[$server]['Pass']),
            'port' => $servers[$server]['Port'],
            'root' => (!is_array($servers[$server]['RemoteDir']) && substr_count($servers[$server]['RemoteDir'], ' ') > 0) ? '' : $servers[$server]['RemoteDir'],
            'passive' => ('MODE_DEFAULT' == $servers[$server]['PasvMode']),
            'timeout' => 10,
        ];
        $site->setFtpInfo($ftp_config);
        $em->persist($site);
        $em->flush();

        $adapter = $site->getAdapter();
        if (!$adapter) {
            return $this->redirectToRoute('admin_site_edit', ['id' => $site->getId()]);
        }
        $filesystem = new Filesystem($adapter);
        if ($adapter->isConnected()) {
            return $this->redirectToRoute('admin_site_edit', ['id' => $site->getId()]);
        }
        $contents = $filesystem->listContents('/', false);
        /*
        var_dump($contents);
        die;
        var_dump($contents);
        die;
*/
        return $this->redirectToRoute('admin_explorer', ['id' => $site->getId()]);
    }
}
