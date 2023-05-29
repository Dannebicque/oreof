<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Synchronisation/But.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 24/05/2023 08:57
 */

namespace App\TypeDiplome\Synchronisation;

use App\Entity\Formation;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class But
{
    public function __construct(
        protected HttpClientInterface   $client,
        protected ParameterBagInterface $container
    ) {
    }

    public function synchroniser(Formation $formation)
    {
        $response = $this->client->request(
            'GET',
            'http://redigetonbut:8888/index.php/api/',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X_API_KEY' => $this->container->get('api_key')
                ]
            ]
        );
    }
}
