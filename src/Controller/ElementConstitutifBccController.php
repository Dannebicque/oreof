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
    #[Route('/{id}/bcc-ec', name: 'app_element_constitutif_bcc', methods: ['GET', 'POST'])]
    public function bccEc(
        EntityManagerInterface       $entityManager,
        CompetenceRepository         $competenceRepository,
        BlocCompetenceRepository     $blocCompetenceRepository,
        TypeDiplomeRegistry          $typeDiplomeRegistry,
        TypeEpreuveRepository        $typeEpreuveRepository,
        Request                      $request,
        ElementConstitutifRepository $elementConstitutifRepository,
        ElementConstitutif           $elementConstitutif
    ): Response {
        $formation = $elementConstitutif->getParcours()?->getFormation();
        if ($formation === null) {
            throw new RuntimeException('Formation non trouvée');
        }
        $ficheMatiere = $elementConstitutif->getFicheMatiere();

        if ($request->isMethod('POST')) {
            $data = JsonRequest::getFromRequest($request);
            if ($data['action'] === 'addCompetence') {
                $competence = $competenceRepository->find($data['value']);

                //on regarde si c'est déjà là, soit dans EC, soit dans fichematiere
                if ($elementConstitutif->isFicheFromParcours()) {
                    $existe = $elementConstitutif->getFicheMatiere()->getCompetences()->contains($competence);
                    if ($existe && (bool)$data['checked'] === false) {
                        $elementConstitutif->getFicheMatiere()->removeCompetence($competence);
                    } elseif (!$existe && (bool)$data['checked'] === true) {
                        $elementConstitutif->getFicheMatiere()->addCompetence($competence);
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
        if ($elementConstitutif->isFicheFromParcours()) {
            $parcours = $elementConstitutif->getFicheMatiere()->getParcours();
            foreach ($ficheMatiere->getCompetences() as $competence) {
                $ecComps[] = $competence->getId();
                $ecBccs[] = $competence->getBlocCompetence()?->getId();
            }
        } else {
            $parcours = $elementConstitutif->getParcours();
            foreach ($elementConstitutif->getCompetences() as $competence) {
                $ecComps[] = $competence->getId();
                $ecBccs[] = $competence->getBlocCompetence()?->getId();
            }
        }


        if ($ficheMatiere->getParcours() !== null) {
            $bccs = $blocCompetenceRepository->findByParcours($parcours);
        } else {
            $bccs = $blocCompetenceRepository->findBy(['parcours' => null]);
        }

        if ($this->isGranted('CAN_FORMATION_EDIT_MY', $formation) ||
            $this->isGranted('CAN_PARCOURS_EDIT_MY', $elementConstitutif->getParcours())) { //todo: ajouter le workflow...
            return $this->render('element_constitutif/_bccEcModal.html.twig', [
                'ec' => $elementConstitutif,
                'bccs' => $bccs,
                'wizard' => false,
                'ecBccs' => array_flip(array_unique($ecBccs)),
                'ecComps' => array_flip($ecComps),
            ]);
        }

        //todo: a faire
        return $this->render('element_constitutif/_bccEcNonEditable.html.twig', [
            'ec' => $elementConstitutif,
            'bccs' => $bccs,
        ]);
    }

//    /**
//     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
//     */
//    public function displayBccEc(
//        TypeDiplomeRegistry   $typeDiplomeRegistry,
//        TypeEpreuveRepository $typeEpreuveRepository,
//        ElementConstitutif    $elementConstitutif
//    ): Response {
//        $formation = $elementConstitutif->getParcours()->getFormation();
//        if ($formation === null) {
//            throw new RuntimeException('Formation non trouvée');
//        }
//        $typeDiplome = $formation->getTypeDiplome();
//
//        if ($typeDiplome === null) {
//            throw new RuntimeException('Type de diplome non trouvé');
//        }
//
//        $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());
//
//        return $this->render('element_constitutif/_mcccEcNonEditable.html.twig', [
//            'ec' => $elementConstitutif,
//            'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
//            'templateForm' => $typeD::TEMPLATE_FORM_MCCC,
//            'mcccs' => $typeD->getMcccs($elementConstitutif),
//        ]);
//    }
}
