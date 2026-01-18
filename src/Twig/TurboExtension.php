<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/TurboExtension.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 12/01/2026 20:53
 */

namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TurboExtension extends AbstractExtension
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_turbo_frame', [$this, 'isTurboFrame']),
        ];
    }

    public function isTurboFrame(): bool
    {
        $req = $this->requestStack->getCurrentRequest();

        if (!$req) {
            return false;
        }

        if ($req->headers->has('Turbo-Frame')) {
            return true;
        }

        $accept = $req->headers->get('Accept', '');
        if (str_contains($accept, 'text/vnd.turbo-stream.html') || str_contains($accept, 'text/vnd.turbo-html')) {
            return true;
        }

        return false;
    }
}

