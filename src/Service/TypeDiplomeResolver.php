<?php
/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Service/TypeDiplomeResolver.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 28/05/2025 15:34
 */

namespace App\Service;

use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\TypeDiplome;
use App\TypeDiplome\TypeDiplomeHandlerInterface;
use LogicException;

final class TypeDiplomeResolver
{

    private TypeDiplomeHandlerInterface $handler;

    /** @param iterable<TypeDiplomeHandlerInterface> $handlers */
    public function __construct(private iterable $handlers)
    {
    }

    public function getFromFormation(?Formation $formation): TypeDiplomeHandlerInterface
    {
        if (null === $formation) {
            throw new LogicException('No formation');
        }

        if (null === $formation->getTypeDiplome()) {
            throw new LogicException('No type diplome');
        }

        return $this->get($formation->getTypeDiplome());
    }

    public function getFromParcours(?Parcours $parcours): TypeDiplomeHandlerInterface
    {
        if (null === $parcours) {
            throw new LogicException('No parcours');
        }

        if (null === $parcours->getFormation()) {
            throw new LogicException('No formation');
        }

        if (null === $parcours->getFormation()->getTypeDiplome()) {
            throw new LogicException('No type diplome');
        }

        return $this->get($parcours->getFormation()->getTypeDiplome());
    }

    public function get(TypeDiplome $type): TypeDiplomeHandlerInterface
    {
        foreach ($this->handlers as $h) {
            if ($h->supports($type->getLibelleCourt())) {
                $this->handler = $h;
                return $h;
            }
        }
        throw new LogicException("No handler for {$type->getLibelle()}");
    }

    public function getTemplateFolder(): string
    {
        return $this->handler::TEMPLATE_FOLDER;
    }
}
