<?php

namespace App\SimplementWeb\SettingsBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

class Setting
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function get(string $key, $default_value = null)
    {
        $setting = $this->entityManager->getRepository('Settings:Setting')->findOneBy(array('name' => $key));

        if ($setting) {
            return $setting->getValue();
        }

        return $default_value;
    }

    public function group($key)
    {
        $settings = [];

        $results = $this->entityManager->getRepository('Settings:Setting')->createQueryBuilder('s')
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

    public function set(string $key, $value = null)
    {
        $setting = $this->entityManager->getRepository('Settings:Setting')->findOneBy(array('name' => $key));

        if ($setting) {
            $setting->setValue($value);
            $this->entityManager->persist($setting);
            $this->entityManager->flush();

            return true;
        }

        if ('' != $value) {
            $setting = new \App\SimplementWeb\SettingsBundle\Entity\Setting();
            $setting->setName($key);
            $setting->setValue($value);
            $this->entityManager->persist($setting);
            $this->entityManager->flush();
        }

        return false;
    }
}
