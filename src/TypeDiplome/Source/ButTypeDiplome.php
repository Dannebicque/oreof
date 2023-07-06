<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Source/ButTypeDiplome.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 15/03/2023 13:41
 */

namespace App\TypeDiplome\Source;

use App\Entity\ElementConstitutif;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Repository\ButCompetenceRepository;
use App\TypeDiplome\Synchronisation\But;
use App\TypeDiplome\TypeDiplomeRegistry;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ButTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'but';
    public const TEMPLATE_FORM_MCCC = 'but.html.twig';

    public string $libelle = 'Bachelor Universitaire de Technologie (B.U.T.)';

    public function __construct(
        protected ButCompetenceRepository $butCompetenceRepository,
        protected EntityManagerInterface $entityManager,
        protected TypeDiplomeRegistry $typeDiplomeRegistry,
        protected But $synchronisationBut
    ) {
        parent::__construct($entityManager, $typeDiplomeRegistry);
    }

    public function getMcccs(ElementConstitutif $elementConstitutif): array|Collection
    {
        return $elementConstitutif->getMcccs();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function synchroniser(
        Formation $formation
    ): bool {
        return $this->synchronisationBut->synchroniser($formation);
    }

    public function getRefCompetences(Parcours $parcours): array|Collection
    {
        return $this->butCompetenceRepository->findBy([
            'formation' => $parcours->getFormation(),
        ]);
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
