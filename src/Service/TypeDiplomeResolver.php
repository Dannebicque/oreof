<?php
/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Service/TypeDiplomeResolver.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 28/05/2025 15:34
 */

namespace App\Service;

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

    public function get(TypeDiplome $type): TypeDiplomeHandlerInterface
    {
        foreach ($this->handlers as $h) {
            if ($h->supports($type->getModeleMcc())) {
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
