<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/TurboTrait.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 12/01/2026 20:53
 */

namespace App\Controller;

use Symfony\Component\HttpFoundation\RequestStack;

trait TurboTrait
{
    private ?RequestStack $requestStack = null;

    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    protected function isTurboFrameRequest(): bool
    {
        if ($this->requestStack === null) {
            return false;
        }

        $req = $this->requestStack->getCurrentRequest();

        if (!$req) {
            return false;
        }

        // Turbo-Frame header présent ?
        if ($req->headers->has('Turbo-Frame')) {
            return true;
        }

        // Accept header pouvant indiquer un turbo-stream
        $accept = $req->headers->get('Accept', '');
        if (str_contains($accept, 'text/vnd.turbo-stream.html') || str_contains($accept, 'text/vnd.turbo-html')) {
            return true;
        }

        return false;
    }
}

