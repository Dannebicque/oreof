<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FicheMatiereController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\EcOrdre;
use App\Classes\GetElementConstitutif;
use App\Classes\JsonReponse;
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use App\Entity\TypeEc;
use App\Entity\Ue;
use App\Form\EcStep4Type;
use App\Form\ElementConstitutifEnfantType;
use App\Form\ElementConstitutifType;
use App\Repository\BlocCompetenceRepository;
use App\Repository\ButApprentissageCritiqueRepository;
use App\Repository\ButCompetenceRepository;
use App\Repository\CompetenceRepository;
use App\Repository\ElementConstitutifRepository;
use App\Repository\FicheMatiereRepository;
use App\Repository\NatureUeEcRepository;
use App\Repository\TypeEcRepository;
use App\Repository\TypeEpreuveRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/element/constitutif')]
class ElementConstitutifBccController extends AbstractController
{
    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{id}/bcc-ec/{parcours}', name: 'app_element_constitutif_bcc', methods: ['GET', 'POST'])]
    public function bccEc(
        ButCompetenceRepository            $butCompetenceRepository,
        ButApprentissageCritiqueRepository $butApprentissageCritiqueRepository,
        EntityManagerInterface             $entityManager,
        CompetenceRepository               $competenceRepository,
        BlocCompetenceRepository           $blocCompetenceRepository,
        Request                            $request,
        ElementConstitutif                 $elementConstitutif,
        Parcours                           $parcours
    ): Response {
        $formation = $elementConstitutif->getParcours()?->getFormation();
        $raccroche = $elementConstitutif->getFicheMatiere()?->getParcours() !== $parcours;
        if ($formation === null) {
            throw new RuntimeException('Formation non trouvée');
        }
        $ficheMatiere = $elementConstitutif->getFicheMatiere();
        $typeDiplome = $formation->getTypeDiplome();

        if ($typeDiplome === null) {
            throw new RuntimeException('Type de diplome non trouvé');
        }

        if ($typeDiplome->getLibelleCourt() === 'BUT') {
            if ($request->isMethod('POST')) {
                $data = JsonRequest::getFromRequest($request);
                if ($data['action'] === 'addCompetence') {
                    $competence = $butApprentissageCritiqueRepository->find($data['value']);
                    if ($competence === null) {
                        return JsonReponse::error('Apprentissage critique non trouvé');
                    }

                    //on regarde si c'est déjà là, soit dans EC, soit dans fichematiere

                    $existe = $elementConstitutif->getFicheMatiere()?->getApprentissagesCritiques()->contains($competence);
                    if ($existe && (bool)$data['checked'] === false) {
                        $elementConstitutif->getFicheMatiere()?->removeApprentissagesCritique($competence);
                    } elseif (!$existe && (bool)$data['checked'] === true) {
                        $elementConstitutif->getFicheMatiere()?->addApprentissagesCritique($competence);
                    }
                    $entityManager->flush();
                }
                return JsonReponse::success('Apprentissage critique ajouté');
            }

            $ecComps = [];
            //tester si BUT ou autre...


            foreach ($elementConstitutif->getFicheMatiere()?->getApprentissagesCritiques() as $competence) {
                if ($competence !== null) {
                    $ecComps[] = $competence->getId();
                }
            }

            $competence = $butCompetenceRepository->findOneByUe($elementConstitutif->getUe(), $formation);
            if ($competence !== null) {
                $apprentissageCritiques = $butApprentissageCritiqueRepository->findByCompetenceSemestre($competence, $elementConstitutif->getUe()?->getSemestre());
            } else {
                $apprentissageCritiques = [];
            }

            if ($this->isGranted('CAN_FORMATION_EDIT_MY', $formation) ||
                $this->isGranted('CAN_PARCOURS_EDIT_MY', $elementConstitutif->getParcours())) { //todo: ajouter le workflow...
                return $this->render('element_constitutif/_bccEcButModal.html.twig', [
                    'competence' => $competence,
                    'ec' => $elementConstitutif,
                    'apprentissageCritiques' => $apprentissageCritiques,
                    'ecComps' => array_flip($ecComps),
                    'parcours' => $parcours
                ]);
            }
        } else {
            if ($request->isMethod('POST') &&
                ($elementConstitutif->isSynchroBcc() === false || $elementConstitutif->getParcours()->getid() === $parcours->getId())) {
                $data = JsonRequest::getFromRequest($request);
                if ($data['action'] === 'addCompetence') {
                    $competence = $competenceRepository->find($data['value']);

                    //on regarde si c'est déjà là, soit dans EC, soit dans fichematiere
                    if ($ficheMatiere !== null && $ficheMatiere->getParcours()?->getId() === $parcours->getId()) {
                        $existe = $ficheMatiere->getCompetences()->contains($competence);
                        if ($existe && (bool)$data['checked'] === false) {
                            $ficheMatiere->removeCompetence($competence);
                        } elseif (!$existe && (bool)$data['checked'] === true) {
                            $ficheMatiere->addCompetence($competence);
                        }
                        $entityManager->flush();
                    } else {
                        $existe = $elementConstitutif->getCompetences()->contains($competence);
                        if ($existe && (bool)$data['checked'] === false) {
                            $elementConstitutif->removeCompetence($competence);
                        } elseif (!$existe && (bool)$data['checked'] === true) {
                            $elementConstitutif->addCompetence($competence);
                        }
                        $entityManager->flush();
                    }
                    return JsonReponse::success('Compétence ajoutée');
                }
                return JsonReponse::error('Action interdite');
            }

            $ecBccs = [];
            $ecComps = [];
            $bccs = $blocCompetenceRepository->findByParcours($parcours);

            if ($raccroche === true && $elementConstitutif->isSynchroBcc() === true) {
                $editable = false;
                //faire le lien entre le parcours d'origine et le parcours en cours via le code des compétences
                // competences du parcours
                $tabCompetences = [];
                foreach ($bccs as $bcc) {
                    foreach ($bcc->getCompetences() as $competence) {
                        $tabCompetences[$competence->getCode()] = $competence;
                    }
                }
                // competences de l'EC du parcours d'origine
                $competences = GetElementConstitutif::getBccs($elementConstitutif, $raccroche);

                //on fait le lien entre la compétence et le code
                foreach ($competences as $competence) {
                    if (array_key_exists($competence->getCode(), $tabCompetences)) {
                        $ecComps[] = $tabCompetences[$competence->getCode()]->getId();
                        $ecBccs[] = $tabCompetences[$competence->getCode()]->getBlocCompetence()?->getId();
                    }
                }
            } else {
                $editable = true;
                if (($ficheMatiere !== null && $ficheMatiere->getParcours()?->getId() === $parcours->getId())) {
                    foreach ($ficheMatiere->getCompetences() as $competence) {
                        $ecComps[] = $competence->getId();
                        $ecBccs[] = $competence->getBlocCompetence()?->getId();
                    }
                } else {
                    foreach ($elementConstitutif->getCompetences() as $competence) {
                        $ecComps[] = $competence->getId();
                        $ecBccs[] = $competence->getBlocCompetence()?->getId();
                    }
                }
            }


            if ($this->isGranted('CAN_FORMATION_EDIT_MY', $formation) ||
                $this->isGranted('CAN_PARCOURS_EDIT_MY', $parcours)) {
                return $this->render('element_constitutif/_bccEcModal.html.twig', [
                    'ec' => $elementConstitutif,
                    'bccs' => $bccs,
                    'wizard' => false,
                    'ecBccs' => array_flip(array_unique($ecBccs)),
                    'ecComps' => array_flip($ecComps),
                    'parcours' => $parcours,
                    'raccroche' => $raccroche,
                    'editable' => $editable
                ]);
            }

            //todo: a faire
            return $this->render('element_constitutif/_bccEcNonEditable.html.twig', [
                'ec' => $elementConstitutif,
                'bccs' => $bccs,
            ]);
        }
    }
}
