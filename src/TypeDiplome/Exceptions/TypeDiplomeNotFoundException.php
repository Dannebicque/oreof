<?php

namespace App\TypeDiplome\Exceptions;

use Exception;

class TypeDiplomeNotFoundException extends Exception
{
    protected $message = 'Ce type de diplôme n\'existe pas';
}
