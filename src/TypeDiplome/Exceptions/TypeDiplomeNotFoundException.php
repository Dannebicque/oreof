<?php
/*
 * Copyright (c) 2022. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/intranetV3/src/Components/PlanCours/Exceptions/PlanCoursNotFoundException.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 18/11/2022 08:54
 */

namespace App\TypeDiplome\Exceptions;

use Exception;

class TypeDiplomeNotFoundException extends Exception
{
    protected $message = 'Ce type de diplôme n\'existe pas';
}
