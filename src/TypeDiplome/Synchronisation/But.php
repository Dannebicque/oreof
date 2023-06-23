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
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\SemestreParcours;
use App\Entity\Ue;
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
    protected bool $type3;
    protected array $listeParcours = [];
    protected array $stucture = [];

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
            $this->container->get('api_url') . '/departements/' . strtolower($formation->getSigle()),
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ]
            ]
        );

        $response2 = $this->client->request(
            'GET',
            $this->container->get('api_url') . '/specialite/' . strtolower($formation->getSigle()) . '/competences?page=1',
            [
                'headers' => [
                    'Accept' => 'application/ld+json',
                    'Content-Type' => 'application/ld+json'
                ]
            ]
        );

        if ($response->getStatusCode() === 200 && $response2->getStatusCode() === 200) {
            $this->clearRefCompetences();
            $this->clearParcours();
            $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $data2 = json_decode($response2->getContent(), true, 512, JSON_THROW_ON_ERROR);


            $this->initFormation($data);

            $this->ecritRefCompetences($data2['hydra:member']);
            $this->initParcours($data);
        }

        $page = 0;
        do {
            $page++;
            $response2 = $this->client->request(
                'GET',
                $this->container->get('api_url') . '/specialite/' . strtolower($formation->getSigle()) . '/ressources?page=' . $page,
                [
                    'headers' => [
                        'Accept' => 'application/ld+json',
                        'Content-Type' => 'application/ld+json'
                    ]
                ]
            );
            $data2 = json_decode($response2->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $this->addRessources($data2['hydra:member']);
        } while ($page * 30 < $data2['hydra:totalItems']);
        // $this->entityManager->flush();

        return true;
    }

    private function ecritRefCompetences(mixed $data): void
    {
        foreach ($data as $competence) {
            $this->ecritRefCompetence($competence);
        }
    }

    private function ecritRefCompetence(mixed $competence)
    {
        $comp = new ButCompetence();
        $comp->setFormation($this->formation);
        $comp->setNomCourt($competence['nom_court']);
        $comp->setLibelle($competence['libelle']);
        $comp->setNumero($competence['numero']);

        $tSituations = [];
        foreach ($competence['apcSituationProfessionnelles'] as $situationProfessionnelle) {
            $tSituations[] = $situationProfessionnelle['libelle'];
        }
        $comp->setSituations($tSituations);

        $tComposantes = [];
        foreach ($competence['apcComposanteEssentielles'] as $composanteEssentielle) {
            $tComposantes[] = $composanteEssentielle['libelle'];
        }
        $comp->setComposantes($tComposantes);

        foreach ($competence['apcNiveaux'] as $niveau) {
            $niv = new ButNiveau();
            $niv->setLibelle($niveau['libelle']);
            $niv->setOrdre($niveau['ordre']);
            $niv->setAnnee($niveau['annee']['libelle']);
            $niv->setCompetence($comp);
            $comp->addButNiveau($niv);
            $this->entityManager->persist($niv);
            foreach ($niveau['apcApprentissageCritiques'] as $acs) {
                $ac = new ButApprentissageCritique();
                $ac->setLibelle($acs['libelle']);
                $ac->setCode($acs['code']);
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

    public function clearParcours(): void
    {
        $sem = [];
        $semP = [];
        foreach ($this->formation->getParcours() as $parcours) {
            foreach ($parcours->getSemestreParcours() as $semestreParcour) {
                $sem[] = $semestreParcour->getSemestre();
                $semP[] = $semestreParcour;
            }
            $this->entityManager->remove($parcours);
        }

        foreach ($sem as $semestre) {
            foreach ($semestre->getUes() as $ue) {
                foreach ($ue->getElementConstitutifs() as $uc) {
                    $this->entityManager->remove($uc);
                }
                $this->entityManager->remove($ue);
            }
            $this->entityManager->remove($semestre);
        }

        foreach ($semP as $semestreParcour) {
            $this->entityManager->remove($semestreParcour);
        }

        $this->entityManager->flush();
    }

    private function initParcours(mixed $data): void
    {
        $tronc = [];
        $addOne = true;

        if (!$this->type3) {
            for ($i = 1; $i <= 2; $i++) {
                $semestre = new Semestre();
                $semestre->setOrdre($i);
                $semestre->setTroncCommun(true);
                $this->entityManager->persist($semestre);
                $tronc[$i] = $semestre;
            }
        }

        foreach ($data['apcParcours'] as $parcours) {
            $this->structure[$parcours['id']] = [];
            $listeSemestre = [];
            $parc = new Parcours($this->formation);
            $parc->setLibelle($parcours['libelle']);
            $parc->setSigle($parcours['code']);
            $parc->setContenuFormation($parcours['textePresentation']);
            $state = $parc->getEtatSteps();
            $state['3'] = true;
            $parc->setEtatSteps($state);
            $this->entityManager->persist($parc);
            $this->listeParcours[$parcours['id']] = $parc;

            //si type 3 idem, si type 1 ou 2 tronc commun
            if ($this->type3) {
                $debut = 1;
            } else {
                $debut = 3;
                for ($i = 1; $i <= 2; $i++) {
                    $semParc = new SemestreParcours($tronc[$i], $parc);
                    $semParc->setOrdre($i);
                    $semParc->setPorteur(true);
                    $this->entityManager->persist($semParc);
                    $tronc[$i]->addSemestreParcour($semParc);

                    $listeSemestre[$i] = $tronc[$i];
                    $this->structure[$parcours['id']][$i] = [];
                }
            }

            //création des semestres
            for ($i = $debut; $i <= 6; $i++) {
                $this->structure[$parcours['id']][$i] = [];
                $semestre = new Semestre();
                $semestre->setOrdre($i);
                $semestre->setTroncCommun(false);
                $this->entityManager->persist($semestre);

                $semParc = new SemestreParcours($semestre, $parc);
                $semParc->setOrdre($i);
                $semParc->setPorteur(true);
                $this->entityManager->persist($semParc);
                $semestre->addSemestreParcour($semParc);

                $listeSemestre[$i] = $semestre;
            }

            //synchron Parcours / Compétences / UE
            foreach ($parcours['apcParcoursNiveaux'] as $apcParcoursNiveau) {
                $annee = $apcParcoursNiveau['niveau']['annee']['libelle'];
                //todo: ne faire qu'une seule fois BUT1 si pas type 3
                if ($annee === 'BUT1') {
                    $offset = 0;
                } elseif ($annee === 'BUT2') {
                    $offset = 2;
                } elseif ($annee === 'BUT3') {
                    $offset = 4;
                }

                if ((!$this->type3 && $addOne === true && $annee === 'BUT1') || $annee !== 'BUT1' || $this->type3) {
                    for ($j = 1; $j <= 2; $j++) {
                        $ue = new Ue();
                        $ue->setOrdre($apcParcoursNiveau['niveau']['competence']['numero']);//numéro de la compétence = ordre
                        $ue->setLibelle($apcParcoursNiveau['niveau']['competence']['nom_court']); //nom court de la compétence
                        $ue->setSemestre($listeSemestre[$offset + $j]);
                        $this->structure[$parcours['id']][$i][$apcParcoursNiveau['niveau']['competence']['numero']] = $ue;
                        $this->entityManager->persist($ue);
                    }

                }
            }
            $addOne = false;
        }
        $this->entityManager->flush();
    }

    private function initFormation(mixed $data): void
    {
        $this->formation->setHasParcours(true);
        $sem = [];
        if ($data['typeStructure'] === 'type_3') {
            $this->type3 = true;
            $sem[1] = 'parcours';
            $sem[2] = 'parcours';
        } else {
            $this->type3 = false;
            $sem[1] = 'tronc_commun';
            $sem[2] = 'tronc_commun';
        }

        for ($i = 3; $i <= 6; $i++) {
            $sem[$i] = 'parcours';
        }

        $this->formation->setStructureSemestres($sem);
    }

    private function addRessources(array $data): void
    {
        foreach ($data as $ressource) {
            $this->addRessource($ressource);
        }
    }

    private function addRessource(array $ressource): void
    {
        dump($ressource);
        $fm = new FicheMatiere();
        $fm->setTypeMatiere(FicheMatiere::TYPE_MATIERE_RESSOURCE);
        $fm->setLibelle($ressource['libelle']);
        $fm->setSigle($ressource['codeMatiere']);
        $fm->setDescription($ressource['description']);
        $fm->setEnseignementMutualise(false);
        //$fm->setbin(??);
        //todo: potentiellement plusieurs parcours...
        //ajouter compétences

        $this->structure[$ressource['parcours']['id']][$ressource['semestre']['ordreLmd']][$ressource['ue']['ordre']]->addFicheMatiere($fm);

die();
       // $this->entityManager->persist($fm);

        // création de l'EC associé
        $ec = new ElementConstitutif();
        $ec->setOrdre($ressource['ordre']);
        // $ec->setUe(??);
        // $ec->setNatureUeEc(??);
        // $ec->setTypeEc(??);
        $ec->setFicheMatiere($fm);
        $ec->genereCode();
        $this->entityManager->persist($fm);
    }
}
