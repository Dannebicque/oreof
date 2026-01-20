<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Utils/TurboStreamResponseFactory.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 18/01/2026 19:33
 */

namespace App\Utils;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class TurboStreamResponseFactory
{
    public function __construct(private Environment $twig)
    {
    }

    public function appendStreams(string ...$streams): Response
    {
        return new Response(
            implode("\n", $streams),
            200,
            ['Content-Type' => 'text/vnd.turbo-stream.html']
        );
    }

    public function streamToastError(string $message): Response
    {
        return $this->stream('_ui/error.toast.stream.html.twig', ['toastMessage' => $message]);
    }

    public function stream(string $template, array $context = [], int $status = 200): Response
    {
        return new Response(
            $this->twig->render($template, $context),
            $status,
            ['Content-Type' => 'text/vnd.turbo-stream.html']
        );
    }
}
