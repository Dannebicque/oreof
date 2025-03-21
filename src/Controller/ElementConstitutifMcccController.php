<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FicheMatiereController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\GetDpeParcours;
use App\Classes\GetElementConstitutif;
use App\Classes\JsonReponse;
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Mccc;
use App\Entity\Parcours;
use App\Entity\ParcoursVersioning;
use App\Entity\TypeDiplome;
use App\Entity\TypeEpreuve;
use App\Events\McccUpdateEvent;
use App\Repository\ElementConstitutifRepository;
use App\Repository\TypeEpreuveRepository;
use App\Service\VersioningParcours;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\Access;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/element/constitutif')]
class ElementConstitutifMcccController extends AbstractController
{
    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{id}/mccc-ec/{parcours}', name: 'app_element_constitutif_mccc', methods: ['GET', 'POST'])]
    public function mcccEc(
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface       $entityManager,
        TypeDiplomeRegistry          $typeDiplomeRegistry,
        TypeEpreuveRepository        $typeEpreuveRepository,
        Request                      $request,
        ElementConstitutifRepository $elementConstitutifRepository,
        ElementConstitutif           $elementConstitutif,
        Parcours                     $parcours
    ): Response {
        //todo: sans doute à simplifier. Les cas sont : EC parent, EC enfant, EC propriétaire de la fiche, EC raccroché ? récupérer les infos du badge ?l
        $dpeParcours = GetDpeParcours::getFromParcours($parcours);
        $isParcoursProprietaire = $elementConstitutif->getFicheMatiere()?->getParcours()?->getId() === $parcours->getId();

        if ($dpeParcours === null) {
            throw new RuntimeException('DPE Parcours non trouvé');
        }

        $formation = $parcours?->getFormation();
        if ($formation === null) {
            throw new RuntimeException('Formation non trouvée');
        }
        $typeDiplome = $formation->getTypeDiplome();
        if ($typeDiplome === null) {
            throw new RuntimeException('Type de diplome non trouvé');
        }
        $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());

        $raccroche = $elementConstitutif->getFicheMatiere()?->getParcours()?->getId() !== $parcours->getId();
        $getElement = new GetElementConstitutif($elementConstitutif, $parcours);
        $typeEpreuve = $getElement->getTypeMcccFromFicheMatiere();

        if ($this->isGranted('CAN_PARCOURS_EDIT_MY', $dpeParcours) && Access::isAccessible($dpeParcours, 'cfvu')) {
            if ($request->isMethod('POST')) {
                $originalMcccToText = $this->mcccToTexte($getElement->getMcccsFromFicheMatiereCollection());
                $originalEcts = $getElement->getFicheMatiereEcts();
                $event = new McccUpdateEvent($elementConstitutif, $parcours);

                if ($request->request->has('ec_step4') && array_key_exists('ects', $request->request->all()['ec_step4'])) {
                    if ($elementConstitutif->isEctsSpecifiques() === true &&
                        $elementConstitutif->getEcParent() === null &&
                        $elementConstitutif->getFicheMatiere()?->isEctsImpose() === false) {
                        $elementConstitutif->setEcts((float)$request->request->all()['ec_step4']['ects']);
                        $newEcts = $elementConstitutif->getEcts();
                    } elseif ($elementConstitutif->getEcParent() !== null) {
                        $elementConstitutif->setEcts($elementConstitutif->getEcParent()?->getEcts());
                        $elementConstitutif->setEctsSpecifiques(true); //du coup ca devient spécifique ?
                        $newEcts = $elementConstitutif->getEcts();
                    } else {
                        $elementConstitutif->getFicheMatiere()?->setEcts((float)$request->request->all()['ec_step4']['ects']);
                        $newEcts = $elementConstitutif->getFicheMatiere()?->getEcts();
                    }

                    //evenement pour ECTS sur EC mis à jour
                    $event->setNewEcts($originalEcts, $newEcts);

                    $entityManager->flush();
                }

                if ($elementConstitutif->isMcccSpecifiques() === false
                   // && $elementConstitutif->getFicheMatiere()?->isMcccImpose() === false
                    && $elementConstitutif->getParcours()->getId() === $parcours->getId()) {
                    //MCCC sur fiche matière
                    if ($elementConstitutif->getFicheMatiere() !== null) {
                        $fm = $elementConstitutif->getFicheMatiere();

                        if ($request->request->has('ec_step4') && array_key_exists('quitus', $request->request->all()['ec_step4'])) {
                            $fm->setQuitus((bool)$request->request->all()['ec_step4']['quitus']);
                        }

                        if ($request->request->get('choix_type_mccc') !== $fm->getTypeMccc()) {
                            $fm->setTypeMccc($request->request->get('choix_type_mccc'));
                            $entityManager->flush();
                            $typeD->clearMcccs($fm);
                        }

                        $typeD->saveMcccs($fm, $request->request);
                        $newMcccToText = $this->mcccToTexte($fm->getMcccs());
                    }
                } else {
                    //MCCC sur EC
                    if ($request->request->has('ec_step4') && array_key_exists('quitus', $request->request->all()['ec_step4'])) {
                        $elementConstitutif->setQuitus((bool)$request->request->all()['ec_step4']['quitus']);
                    }

                    if ($request->request->get('choix_type_mccc') !== $elementConstitutif->getTypeMccc()) {
                        $elementConstitutif->setTypeMccc($request->request->get('choix_type_mccc'));
                        $entityManager->flush();
                        $typeD->clearMcccs($elementConstitutif);
                    }
                    $typeD->saveMcccs($elementConstitutif, $request->request);
                    $newMcccToText = $this->mcccToTexte($elementConstitutif->getMcccs());
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
                $entityManager->flush();

                //evenement pour MCCC sur EC mis à jour
                $event = new McccUpdateEvent($elementConstitutif, $parcours);
                $event->setNewMccc($originalMcccToText, $newMcccToText);
                $eventDispatcher->dispatch($event, McccUpdateEvent::UPDATE_MCCC);

                return $this->json(true);
            }

            return $this->render('element_constitutif/_mcccEcModal.html.twig', [
                'isMcccImpose' => $elementConstitutif->getFicheMatiere()?->isMcccImpose(),
                'isEctsImpose' => $elementConstitutif->getFicheMatiere()?->isEctsImpose(),
                'typeMccc' => $typeEpreuve,
                'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
                'ec' => $elementConstitutif,
                'ects' => $getElement->getFicheMatiereEcts(),
                'templateForm' => $typeD::TEMPLATE_FORM_MCCC,
                'mcccs' => $getElement->getMcccsFromFicheMatiereCollection($typeD),
                'wizard' => false,
                'typeDiplome' => $typeDiplome,
                'parcours' => $parcours,
                'isParcoursProprietaire' => $isParcoursProprietaire,
                'raccroche' => $raccroche
            ]);
        }

        $ec = new GetElementConstitutif($elementConstitutif, $parcours);
        $ects = $ec->getFicheMatiereEcts();

        return $this->render('element_constitutif/_mcccEcNonEditable.html.twig', ['isMcccImpose' => $elementConstitutif->getFicheMatiere()?->isMcccImpose(),
        'isEctsImpose' => $elementConstitutif->getFicheMatiere()?->isEctsImpose(),
        'typeMccc' => $typeEpreuve,
        'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
        'ec' => $elementConstitutif,
        'ects' => $ects,
        'templateForm' => $typeD::TEMPLATE_FORM_MCCC,
        'mcccs' => $getElement->getMcccsFromFicheMatiereCollection(),]);
    }

    #[Route('/{id}/mccc-ec/{parcours}/non-editable/{codeVersion}', name: 'app_element_constitutif_mccc_non_editable', methods: ['GET', 'POST'])]
    public function mcccEcNonEditable(
        TypeDiplomeRegistry          $typeDiplomeRegistry,
        TypeEpreuveRepository        $typeEpreuveRepository,
        ElementConstitutif           $elementConstitutif,
        Parcours                     $parcours,
        string                       $codeVersion = "",
        EntityManagerInterface       $entityManager
    ): Response {

        $dpeParcours = GetDpeParcours::getFromParcours($parcours);

        if ($dpeParcours === null) {
            throw new RuntimeException('DPE Parcours non trouvé');
        }

        $formation = $parcours?->getFormation();
        if ($formation === null) {
            throw new RuntimeException('Formation non trouvée');
        }
        $typeDiplome = $formation->getTypeDiplome();
        if ($typeDiplome === null) {
            throw new RuntimeException('Type de diplome non trouvé');
        }
        $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());

        $getElement = new GetElementConstitutif($elementConstitutif, $parcours);
        $typeEpreuve = $getElement->getTypeMcccFromFicheMatiere();
        $ects = $getElement->getFicheMatiereEcts();

        $lastVersion = $entityManager->getRepository(ParcoursVersioning::class)->findLastCfvuVersion($parcours);
        $lastVersion = isset($lastVersion[0]) ? $lastVersion[0] : null;

        return $this->render('element_constitutif/_mcccEcNonEditable.html.twig', [
            'isMcccImpose' => $elementConstitutif->getFicheMatiere()?->isMcccImpose(),
            'isEctsImpose' => $elementConstitutif->getFicheMatiere()?->isEctsImpose(),
            'typeMccc' => $typeEpreuve,
            'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
            'ec' => $elementConstitutif,
            'ects' => $ects,
            'templateForm' => $typeD::TEMPLATE_FORM_MCCC,
            'mcccs' => $getElement->getMcccsFromFicheMatiere($typeD),
            'lastVersion' => $lastVersion,
            'codeVersion' => $codeVersion,
            'isFromVersioning' => 0,
            'libelleQuelleVersion' => 'Version actuelle'
        ]);
    }

    #[Route('/{UeDisplay}/{indexEc}/{indexEcEnfant}/{elementConstitutif}/mccc-ec-versioning/{parcoursVersioning}/non-editable/{isFromVersioning}', name: 'app_element_constitutif_mccc_versioning')]
    public function mcccVersioning(
        string $UeDisplay,
        int $indexEc,
        int $indexEcEnfant,
        int $isFromVersioning,
        ElementConstitutif|int $elementConstitutif = -1,
        ParcoursVersioning $parcoursVersioning,
        TypeDiplomeRegistry $typeDiplomeReg,
        VersioningParcours $versioningParcours,
        EntityManagerInterface $entityManager
    ){
        $versionData = $versioningParcours->loadParcoursFromVersion($parcoursVersioning);
        $libelleTypeDiplome = $versionData['parcours']->getFormation()->getTypeDiplome()->getLibelle();
        $typeDiplome = $entityManager->getRepository(TypeDiplome::class)->findOneBy(['libelle' => $libelleTypeDiplome]);
        $templateForm = $typeDiplomeReg->getTypeDiplome($typeDiplome->getModeleMcc())::TEMPLATE_FORM_MCCC;
        $typeEpreuveDiplome = $entityManager->getRepository(TypeEpreuve::class)->findByTypeDiplome($typeDiplome);

        // On rassemble toutes les UE
        $ueArray = array_map(function($semestre) use ($UeDisplay) {
            $ueFromSemestre = [...$semestre->ues];
            $uesEnfants = array_filter(
                array_map(
                    fn($ue) => $ue->uesEnfants(), 
                    $ueFromSemestre
                ), 
                fn($array) => count($array) > 0);
            $uesEnfants = array_merge(...$uesEnfants);
            $uesEnfantsDeuxiemeNiveau = array_filter(
                array_map(
                    fn($ueEnfant) => $ueEnfant->uesEnfants(), 
                    $uesEnfants
                ),
                fn($array) => count($array) > 0
            );
            $uesEnfantsDeuxiemeNiveau = array_merge(...$uesEnfantsDeuxiemeNiveau);
            return array_merge($ueFromSemestre, $uesEnfants, $uesEnfantsDeuxiemeNiveau);
        }, $versionData['dto']->semestres);

        $ueArray = array_merge(...$ueArray);

        // Et on cherche le StructureEc qui fait partie de l'UE à l'index précisé
        $structureEc = null;
        foreach($ueArray as $ue){
            if($ue->display === $UeDisplay){
                if($indexEcEnfant !== -1){
                    $indexKey = array_keys($ue->elementConstitutifs[$indexEc]->elementsConstitutifsEnfants);
                    $structureEc = $ue->elementConstitutifs[$indexEc]->elementsConstitutifsEnfants[$indexKey[$indexEcEnfant]];
                }else {
                    $structureEc = $ue->elementConstitutifs[$indexEc];
                }   
            }
        }

        // Mise en forme du tableau des MCCC
        $tabMcccs = [];
        if ($structureEc->typeMccc === 'cci') {
            foreach ($structureEc->mcccs as $mccc) {
                $tabMcccs[$mccc['numeroSession']] = $mccc;
            }
        } else {
            foreach ($structureEc->mcccs as $mccc) {
                if ($mccc['secondeChance']) {
                    $tabMcccs[3]['chance'] = $mccc;
                } elseif ($mccc['controleContinu'] === true && $mccc['examenTerminal'] === false) {
                    $tabMcccs[$mccc['numeroSession']]['cc'][$mccc['numeroEpreuve'] ?? 1] = $mccc;
                } elseif ($mccc['controleContinu'] === false && $mccc['examenTerminal'] === true) {
                    $tabMcccs[$mccc['numeroSession']]['et'][$mccc['numeroEpreuve'] ?? 1] = $mccc;
                }
            }
        }

        $codeVersion = $UeDisplay . "##" . $indexEc . "##";
        $codeVersion .= isset($indexEcEnfant) ? "{$indexEcEnfant}" : "-1";

        return $this->render('element_constitutif/_mcccEcNonEditable.html.twig', [
            'isMcccImpose' => $structureEc->elementConstitutif->getFicheMatiere()?->isMcccImpose(),
            'isEctsImpose' => $structureEc->elementConstitutif->getFicheMatiere()?->isEctsImpose(),
            'typeMccc' => $structureEc->typeMccc,
            'typeEpreuves' => $typeEpreuveDiplome,
            'ec' => $structureEc->elementConstitutif,
            'ects' => $structureEc->heuresEctsEc->ects,
            'templateForm' => $templateForm,
            'mcccs' => $tabMcccs,
            'codeVersion' => $codeVersion,
            'isMcccFromVersion' => true,
            'parcoursId' => $parcoursVersioning->getParcours()->getId(),
            'ecFromDb' => $elementConstitutif,
            'isFromVersioning' => $isFromVersioning,
            'libelleQuelleVersion' => 'Version validée CFVU'
        ]);
    }

    #[Route('/{id}/mccc-ec-but', name: 'app_element_constitutif_mccc_but', methods: ['GET', 'POST'])]
    public function mcccEcBut(
        TypeDiplomeRegistry   $typeDiplomeRegistry,
        TypeEpreuveRepository $typeEpreuveRepository,
        Request               $request,
        FicheMatiere          $ficheMatiere
    ): Response {
        $formation = $ficheMatiere->getParcours()?->getFormation();
        if ($formation === null) {
            throw new RuntimeException('Formation non trouvée');
        }
        $typeDiplome = $formation->getTypeDiplome();
        if ($typeDiplome === null) {
            throw new RuntimeException('Type de diplome non trouvé');
        }
        $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());

        if ($this->isGranted('CAN_FORMATION_EDIT_MY', $formation) ||
            $this->isGranted('CAN_PARCOURS_EDIT_MY', $ficheMatiere->getParcours())) { //todo: ajouter le workflow...
            if ($request->isMethod('POST')) {
                $typeD->saveMcccs($ficheMatiere, $request->request);
                return JsonReponse::success('MCCCs enregistrés');
            }

            return $this->render('element_constitutif/_mcccEcModalBut.html.twig', [
                'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
                'ficheMatiere' => $ficheMatiere,
                'templateForm' => $typeD::TEMPLATE_FORM_MCCC,
                'mcccs' => $typeD->getMcccs($ficheMatiere),
                'wizard' => false,
                'typeDiplome' => $typeDiplome,
            ]);
        }
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

    private function mcccToTexte(Collection $getMcccs): string
    {
        //sérialisation de tous les champs de l'entité MCCC
        $mcccs = [];
        /** @var Mccc $mccc */
        foreach ($getMcccs as $mccc) {
            $mcccs[] = $mccc->getMcccTexte();
        }

        return implode(';', $mcccs);


    }
}
