<?php

namespace App\Classes;

use App\Entity\Formation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class UpdateEntity
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function saveCheckbox(
        Formation $formation,
        string $champ,
        mixed $value,
        mixed $isChecked,
        ServiceEntityRepository $repository
    ) {
        $site = $repository->find($value);
        if ($site !== null) {
            if ($isChecked) {
                $method = 'add'.ucfirst($champ);
                if (method_exists($formation, $method)) {
                    $formation->$method($site);
                }
            } else {
                $method = 'remove'.ucfirst($champ);
                if (method_exists($formation, $method)) {
                    $formation->$method($site);
                }
            }
            $this->entityManager->flush();
            return true;
        }
        return false;
    }

    public function saveYesNo(
        Formation $formation,
        string $champ,
        mixed $value
    ) {
        $method = 'set'.ucfirst($champ);
        if (method_exists($formation, $method)) {
            $formation->$method((bool) $value);
            $this->entityManager->flush();
            return true;
        }
        return false;
    }

    public function saveField(
        Formation $formation,
        string $champ,
        mixed $value
    ) {
        $method = 'set'.ucfirst($champ);
        if (method_exists($formation, $method)) {
            $formation->$method($value);
            $this->entityManager->flush();
            return true;
        }
        return false;
    }


}
