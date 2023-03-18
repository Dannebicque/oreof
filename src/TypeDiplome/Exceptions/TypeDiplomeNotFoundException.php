<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Exceptions/TypeDiplomeNotFoundException.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 27/01/2023 19:20
 */

namespace App\TypeDiplome\Exceptions;

use Exception;

class TypeDiplomeNotFoundException extends Exception
{
    protected $message = 'Ce type de diplôme n\'existe pas';
}
