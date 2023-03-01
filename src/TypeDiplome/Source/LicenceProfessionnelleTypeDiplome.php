<?php

namespace App\TypeDiplome\Source;

use App\Entity\ElementConstitutif;
use App\Entity\Formation;
use App\Entity\Parcours;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\InputBag;

class LicenceProfessionnelleTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'licence_professionnelle';
    public const TEMPLATE = 'lp.html.twig';
    public const TEMPLATE_FORM_MCCC = 'lp.html.twig';

    public string $libelle = 'Licence Professionnelle';

    public int $nbSemestres = 6;
    public int $nbUes = 0;

    public function getMcccs(ElementConstitutif $elementConstitutif): array|Collection
    {
        return $elementConstitutif->getMcccs();
    }

    public function initMcccs(ElementConstitutif $elementConstitutif): void
    {
        // TODO: Implement initMcccs() method.
    }

    public function saveMccc(ElementConstitutif $elementConstitutif, string $field, mixed $value): void
    {
        // TODO: Implement saveMccc() method.
    }

    public function saveMcccs(ElementConstitutif $elementConstitutif, InputBag $request): void
    {
        // TODO: Implement saveMcccs() method.
    }
}
