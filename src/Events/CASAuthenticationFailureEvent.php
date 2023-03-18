<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/CASAuthenticationFailureEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/02/2023 22:12
 */

namespace App\Events;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\EventDispatcher\Event;

class CASAuthenticationFailureEvent extends Event
{
    public function __construct(private readonly AuthenticationException $exception, private Response $response)
    {
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function getException(): AuthenticationException
    {
        return $this->exception;
    }

    public function getExceptionType(): string
    {
        return $this->exception::class;
    }
}
