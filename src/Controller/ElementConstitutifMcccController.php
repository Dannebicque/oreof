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
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use App\Entity\TypeEc;
use App\Entity\Ue;
use App\Form\EcStep4Type;
use App\Form\ElementConstitutifEnfantType;
use App\Form\ElementConstitutifType;
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
class ElementConstitutifMcccController extends AbstractController
{
    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{id}/mccc-ec', name: 'app_element_constitutif_mccc', methods: ['GET', 'POST'])]
    public function mcccEc(
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
        $typeDiplome = $formation->getTypeDiplome();
        if ($typeDiplome === null) {
            throw new RuntimeException('Type de diplome non trouvé');
        }
        $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());

        if ($this->isGranted('CAN_FORMATION_EDIT_MY', $formation) ||
            $this->isGranted('CAN_PARCOURS_EDIT_MY', $elementConstitutif->getParcours())) { //todo: ajouter le workflow...
            if ($request->isMethod('POST')) {
                if ($request->request->has('ec_step4') && array_key_exists('ects', $request->request->all()['ec_step4'])) {
                    $elementConstitutif->setEcts((float)$request->request->all()['ec_step4']['ects']);
                } else {
                    $elementConstitutif->setEcts($elementConstitutif->getEcParent()?->getEcts());
                }

                if ($request->request->has('ec_step4') && array_key_exists('quitus', $request->request->all()['ec_step4'])) {
                    $elementConstitutif->setQuitus((bool)$request->request->all()['ec_step4']['quitus']);
                } else {
                    $elementConstitutif->setQuitus(false);
                }

                if ($request->request->has('ec_step4') && array_key_exists('mcccEnfantsIdentique', $request->request->all()['ec_step4'])) {
                    if ($elementConstitutif->getEcParent() !== null) {
                        $elementConstitutif->getEcParent()->setMcccEnfantsIdentique((bool)$request->request->all()['ec_step4']['mcccEnfantsIdentique']);
                    } else {
                        $elementConstitutif->setMcccEnfantsIdentique((bool)$request->request->all()['ec_step4']['mcccEnfantsIdentique']);
                    }
                } else {
                    $elementConstitutif->setMcccEnfantsIdentique(false);
                }

                if ($request->request->get('choix_type_mccc') !== $elementConstitutif->getTypeMccc()) {
                    $elementConstitutif->setTypeMccc($request->request->get('choix_type_mccc'));
                    $elementConstitutifRepository->save($elementConstitutif, true);
                    $typeD->clearMcccs($elementConstitutif);
                    $typeD->initMcccs($elementConstitutif);
                }

                $typeD->saveMcccs($elementConstitutif, $request->request);

                return $this->json(true);
            }

            return $this->render('element_constitutif/_mcccEcModal.html.twig', [
                'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
                'ec' => $elementConstitutif,
                'templateForm' => $typeD::TEMPLATE_FORM_MCCC,
                'mcccs' => $typeD->getMcccs($elementConstitutif),
                'wizard' => false
            ]);
        }

        return $this->render('element_constitutif/_mcccEcNonEditable.html.twig', [
            'ec' => $elementConstitutif,
            'templateForm' => $typeD::TEMPLATE_FORM_MCCC,
        ]);
    }

//    /**
//     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
//     */
//    public function displayMcccEc(
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