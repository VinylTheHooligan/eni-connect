<?php

namespace App\Services\Admin;

use App\Entity\Campus;
use Doctrine\ORM\EntityManagerInterface;

class CampusManager
{
    public function __construct(
        private EntityManagerInterface $em,
    )
    {}

    public function save(Campus $campus)
    {
        $this->em->persist($campus);
        $this->em->flush();
    }

    public function remove(Campus $campus)
    {
        $this->em->remove($campus);
        $this->em->flush();
    }
}