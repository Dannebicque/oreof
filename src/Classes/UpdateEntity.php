<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/UpdateEntity.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Classes;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class UpdateEntity
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function saveCheckbox(
        object $formation,
        string $champ,
        mixed $value,
        mixed $isChecked,
        ServiceEntityRepository $repository
    ): bool {
        $ville = $repository->find($value);
        if ($ville !== null) {
            if ($isChecked) {
                $method = 'add' . ucfirst($champ);
            } else {
                $method = 'remove' . ucfirst($champ);
            }
            if (method_exists($formation, $method)) {
                $formation->$method($ville);
            }
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    public function saveYesNo(
        object $formation,
        string $champ,
        mixed $value
    ): bool {
        $method = 'set' . ucfirst($champ);
        if (method_exists($formation, $method)) {
            $formation->$method((bool)$value);
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    public function saveField(
        object $formation,
        string $champ,
        mixed $value
    ): bool {
        $method = 'set' . ucfirst($champ);
        if (method_exists($formation, $method)) {
            $formation->$method($value);
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    public function addToArray(object $formation, string $champ, mixed $value): bool
    {
        $setMethod = 'set' . ucfirst($champ);
        $getMethod = 'get' . ucfirst($champ);
        if (method_exists($formation, $setMethod) && method_exists($formation, $getMethod)) {
            $t = $formation->$getMethod();
            if (is_array($t)) {
                $t[] = $value;
                $formation->$setMethod($t);
                $this->entityManager->flush();

                return true;
            }

            if ($t === null) {
                $t = [];
                $t[] = $value;
                $formation->$setMethod($t);
                $this->entityManager->flush();

                return true;
            }
        }

        return false;
    }

    public function removeToArray(object $formation, string $champ, mixed $value): bool
    {
        $setMethod = 'set' . ucfirst($champ);
        $getMethod = 'get' . ucfirst($champ);
        if (method_exists($formation, $setMethod) && method_exists($formation, $getMethod)) {
            $t = $formation->$getMethod();
            if (is_array($t)) {
                foreach ($t as $key => $v) {
                    if (is_object($v)) {
                        if ($v->value === $value) {
                            unset($t[$key]);
                        }
                    } elseif ($v === $value) {
                        unset($t[$key]);
                    }
                }

                $formation->$setMethod($t);
                $this->entityManager->flush();

                return true;
            }
        }

        return false;
    }
}
