<?php

namespace App\Workflow\Handler;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class TransitionHandlerRegistry
{
    /** @param iterable<TransitionHandlerInterface|TransitionFicheHandlerInterface> $handlers */
    public function __construct(
        #[AutowireIterator('app.workflow_transition_handler')]
        private iterable $handlers)
    {
    }

    public function get(string $code): TransitionHandlerInterface|TransitionFicheHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($code)) {
                return $handler;
            }
        }
        throw new \LogicException(sprintf('Aucun handler trouvé pour "%s"', $code));
    }
}
