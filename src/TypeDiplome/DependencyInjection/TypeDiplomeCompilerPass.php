<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/DependencyInjection/TypeDiplomeCompilerPass.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 27/01/2023 19:20
 */

namespace App\TypeDiplome\DependencyInjection;

use App\TypeDiplome\TypeDiplomeRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class TypeDiplomeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $registry = $container->getDefinition(TypeDiplomeRegistry::class);
        $this->addToRegistry($container, $registry, TypeDiplomeRegistry::TAG_TYPE_DIPLOME, 'registerTypeDiplome');
    }

    private function addToRegistry(ContainerBuilder $container, Definition $registry, string $tag, string $method): void
    {
        $taggedServices = $container->findTaggedServiceIds($tag);

        foreach ($taggedServices as $id => $tags) {
            $registry->addMethodCall($method, [$id, new Reference($id)]);
        }
    }
}
