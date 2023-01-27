<?php

namespace App\TypeDiplome\Source;

use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractTypeDiplome
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    )
    {
    }
}
