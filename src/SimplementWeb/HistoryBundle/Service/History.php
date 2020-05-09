<?php

namespace App\SimplementWeb\HistoryBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

class History
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function forObject($object)
    {
        if (method_exists($object, 'getClassName')) {
            $object_type = $object->getClassName();
        } else {
            $object_type = get_class($object);
        }

        return $this->entityManager->getRepository('History:history')->findBy([
                'object_type' => $object_type,
                'object_id' => $object->getId(),
            ],
            ['updated' => 'DESC']
        );
    }

    public function forUser($user_id)
    {
        return $this->entityManager->getRepository('History:history')->findBy([
                'user_id' => $user_id,
            ],
            ['updated' => 'DESC']
        );
    }

    public function add($name, $object = null, $info = '', $data = '', $status = 0, $level = 0)
    {
        $history = new \App\SimplementWeb\HistoryBundle\Entity\History();
        $history->setName($name);
        /*
        if ($this->getUser()) {
            $history->setUser($this->getUser());
        }*/

        if (!is_null($object)) {
            if (method_exists($object, 'getClassName')) {
                $history->setObjectType($object->getClassName());
            } else {
                $history->setObjectType(get_class($object));
            }
            $history->setObjectId($object->getId());
        }

        $history->setInfo($info);
        $history->setData($data);
        $history->setStatus($status);
        $history->setLevel($level);

        $this->entityManager->persist($history);
        $this->entityManager->flush();

        return $history;
    }
}
