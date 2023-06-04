<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Synchronisation/But.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 24/05/2023 08:57
 */

namespace App\TypeDiplome\Synchronisation;

use App\Entity\ButApprentissageCritique;
use App\Entity\ButCompetence;
use App\Entity\ButNiveau;
use App\Entity\Formation;
use App\Entity\Parcours;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class But
{
    protected Formation $formation;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected HttpClientInterface    $client,
        protected ParameterBagInterface  $container
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function synchroniser(Formation $formation)
    {
        $this->formation = $formation;
        $response = $this->client->request(
            'GET',
            'http://redigetonbut:8888/index.php/api/' . $formation->getSigle(),
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X_API_KEY' => $this->container->get('api_key')
                ]
            ]
        );

        if ($response->getStatusCode() === 200) {
            $this->clearRefCompetences();
            $this->clearParcours();
            $data = json_decode($response->getContent(), true);
            $this->initFormation($data);
            $this->ecritRefCompetences($data);
            $this->initParcours($data);
            return true;
        }

        return null;
    }

    private function ecritRefCompetences(mixed $data): void
    {
        foreach ($data['competences'] as $competence) {
            $this->ecritRefCompetence($competence);
        }
    }

    private function ecritRefCompetence(mixed $competence)
    {
        $comp = new ButCompetence();
        $comp->setFormation($this->formation);
        $comp->setNomCourt($competence['nom_court']);
        $comp->setLibelle($competence['libelle_long']);
        $comp->setNumero($competence['numero']);

        $comp->setSituations($competence['situations']);
        $comp->setComposantes($competence['composantes']);

        foreach ($competence['niveaux'] as $niveau) {
            $niv = new ButNiveau();
            $niv->setLibelle($niveau['libelle']);
            $niv->setOrdre($niveau['ordre']);
            $niv->setAnnee($niveau['annee']);
            $niv->setCompetence($comp);
            $comp->addButNiveau($niv);
            $this->entityManager->persist($niv);
            foreach ($niveau['acs'] as $key => $libelle) {
                $ac = new ButApprentissageCritique();
                $ac->setLibelle($libelle);
                $ac->setCode($key);
                $ac->setNiveau($niv);
                $niv->addButApprentissageCritique($ac);
                $this->entityManager->persist($ac);
            }
        }

        $this->entityManager->persist($comp);
        $this->entityManager->flush();
    }

    private function clearRefCompetences()
    {
        foreach ($this->formation->getButCompetences() as $competence) {
            foreach ($competence->getButNiveaux() as $niveau) {
                foreach ($niveau->getButApprentissageCritiques() as $ac) {
                    $this->entityManager->remove($ac);
                }
                $this->entityManager->remove($niveau);
            }
            $this->entityManager->remove($competence);
        }
        $this->entityManager->flush();
    }

    public function clearParcours()
    {
        foreach ($this->formation->getParcours() as $parcours) {
            $this->entityManager->remove($parcours);
        }
        $this->entityManager->flush();
    }

    private function initParcours(mixed $data)
    {
        foreach ($data['parcours'] as $parcours) {
            $parc = new Parcours($this->formation);
            $parc->setLibelle($parcours['libelle']);
            $parc->setSigle($parcours['code']);
            $state = $parc->getEtatSteps();
            $state['3'] = true;
            $parc->setEtatSteps($state);
            $this->entityManager->persist($parc);
        }
        $this->entityManager->flush();
    }

    private function initFormation(mixed $data)
    {
        $this->formation->setHasParcours(true);
        $sem = [];
        if ($data['specialite']['type_structure'] === 'type_3') {
            $sem[1] = 'parcours';
            $sem[2] = 'parcours';
        } else {
            $sem[1] = 'tronc_commun';
            $sem[2] = 'tronc_commun';
        }
        $sem[3] = 'parcours';
        $sem[4] = 'parcours';
        $sem[5] = 'parcours';
        $sem[6] = 'parcours';

        $this->formation->setStructureSemestres($sem);
    }
}
