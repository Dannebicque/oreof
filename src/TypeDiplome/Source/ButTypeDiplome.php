<?php

namespace App\TypeDiplome\Source;

use App\Entity\ElementConstitutif;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\InputBag;

class ButTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'but';
    public const TEMPLATE = 'but.html.twig';
    public const TEMPLATE_FORM_MCCC = 'but.html.twig';

    public string $libelle = 'Bachelor Universitaire de Technologie (B.U.T.)';
    public int $nbSemestres = 6;

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
