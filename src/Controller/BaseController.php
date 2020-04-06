<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\SettingRepository;
use App\Entity\Setting;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class BaseController extends AbstractController
{
    public $settingRepository;
    public $userRepository;
    public $passwordEncoder;

    public function __construct(SettingRepository $settingRepository, SettingRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->settingRepository = $settingRepository;
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
    }

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

        $setting = $this->settingRepository->findOneBy(array('name' => $key));

        if ($setting) {
            return $setting->getValue();
        }

        return $default_value;
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

        $setting = $this->settingRepository->findOneBy(array('name' => $key));

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

    public function wrapMail($content, $subject)
    {
        $email_layout = $this->getSetting('email_layout');

        $content = str_replace('[CONTENT]', $content, $email_layout);
        $content = str_replace('[SUBJECT]', $subject, $content);

        return $content;
    }

    public function call($url, $headers, $posts)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, rtrim($url, '/').'/sw.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_REFERER, 'https://www.simplement-web.com');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $posts);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        $result_array = json_decode(trim($body), true);
        if (json_last_error()) {
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            var_dump($header);
            var_dump($body);
        }
        curl_close($ch);

        return $result_array;
    }
}
