<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Synchronisation/But.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 24/05/2023 08:57
 */

namespace App\TypeDiplome\Synchronisation;

use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class But
{
    public function __construct(
        protected HttpClientInterface $client)
    {
    }

    public function synchroniser(\App\Entity\Formation $formation)
    {
        $response = $this->client->request(
            'GET',
            'http://127.0.0.1:8001/fr/api/unifolio/semestre/liste',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X_API_KEY' => $this->getParameter('api_key')
                ]
            ]
        );

        dump($response->getContent());
        die();

    }
}
