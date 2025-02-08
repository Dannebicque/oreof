<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/TypeDiplome/LicenceController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 15/04/2023 22:19
 */

namespace App\Controller\TypeDiplome;

use App\Classes\GetElementConstitutif;
use App\Classes\JsonReponse;
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use App\Repository\ElementConstitutifRepository;
use App\Repository\TypeDiplomeRepository;
use App\Repository\TypeEpreuveRepository;
use App\TypeDiplome\Source\LicenceTypeDiplome;
use App\TypeDiplome\TypeDiplomeRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LicenceController extends AbstractController
{
    #[Route('/type_diplome/change/licence/hd/{ficheMatiere}', name: 'type_diplome_licence_change_hd')]
    public function changeTypeHd(
        Request $request,
        EntityManagerInterface $entityManager,
        LicenceTypeDiplome $licenceTypeDiplome,
        TypeDiplomeRegistry $typeDiplomeRegistry,
        TypeDiplomeRepository $typeDiplomeRepository,
        TypeEpreuveRepository $typeEpreuveRepository,
        FicheMatiere $ficheMatiere,
    ) {
        $typeDiplome = $typeDiplomeRepository->find(1);

        if ($typeDiplome === null) {
            throw new \Exception('Type de diplome non trouvé');
        }

        $typeEpreuves = $typeEpreuveRepository->findByTypeDiplome($typeDiplome);

        if ($request->query->get('type') !== $ficheMatiere->getTypeMccc()) {
            $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());
            $ficheMatiere->setTypeMccc($request->query->get('type'));
            $typeD->clearMcccs($ficheMatiere);
            $entityManager->flush();
        }

        switch ($request->query->get('type')) {
            case 'cc':
                if ($typeDiplome->getLibelleCourt() !== 'L') {
                    //seul cas particulier, pour les autres mêmes formulaires
                    return $this->render('typeDiplome/mccc/licence/_cc_autres_diplomes.html.twig', [
                        'mcccs' => $licenceTypeDiplome->getMcccs($ficheMatiere),
                        'typeEpreuves' => $typeEpreuves,
                        'disabled' => false,
                    ]);
                }

                return $this->render('typeDiplome/mccc/licence/_cc.html.twig', [
                    'mcccs' => $licenceTypeDiplome->getMcccs($ficheMatiere),
                    'typeEpreuves' => $typeEpreuves,
                    'disabled' => false,
                ]);

            case 'cci':
                return $this->render('typeDiplome/mccc/licence/_cci.html.twig', [
                    'mcccs' => $licenceTypeDiplome->getMcccs($ficheMatiere),
                    'typeEpreuves' => $typeEpreuves,
                    'disabled' => false,
                ]);
            case 'cc_ct':
                return $this->render('typeDiplome/mccc/licence/_cc_ct.html.twig', [
                    'mcccs' => $licenceTypeDiplome->getMcccs($ficheMatiere),
                    'typeEpreuves' => $typeEpreuves,
                    'disabled' => false,
                ]);
            case 'ct':
                return $this->render('typeDiplome/mccc/licence/_ct.html.twig', [
                    'mcccs' => $licenceTypeDiplome->getMcccs($ficheMatiere),
                    'typeEpreuves' => $typeEpreuves,
                    'disabled' => false,
                ]);
        }

        return $this->render('typeDiplome/mccc/licence/_vide.html.twig', [
        ]);
    }

    #[Route('/type_diplome/change/licence/{elementConstitutif}/{parcours}', name: 'type_diplome_licence_change')]
    public function changeType(
        Request $request,
        EntityManagerInterface $entityManager,
        LicenceTypeDiplome $licenceTypeDiplome,
        TypeDiplomeRegistry $typeDiplomeRegistry,
        TypeEpreuveRepository $typeEpreuveRepository,
        ElementConstitutifRepository $elementConstitutifRepository,
        ElementConstitutif $elementConstitutif,
        Parcours $parcours
    ) {
        $typeDiplome = $elementConstitutif->getParcours()->getFormation()->getTypeDiplome();

        if ($typeDiplome === null) {
            throw new \Exception('Type de diplome non trouvé');
        }

        $typeEpreuves = $typeEpreuveRepository->findByTypeDiplome($typeDiplome);
        $raccroche = $elementConstitutif->getFicheMatiere()?->getParcours()?->getId() !== $parcours->getId();
        $getElement = new GetElementConstitutif($elementConstitutif, $parcours);
        $disabled = ($elementConstitutif->isMcccSpecifiques() === false && $raccroche) || $elementConstitutif->getFicheMatiere()?->isMcccImpose();

        if ($request->query->get('type') !== $elementConstitutif->getTypeMccc()) {
            $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());
            $elementConstitutif->setTypeMccc($request->query->get('type'));
            $elementConstitutifRepository->save($elementConstitutif, true);
            $typeD->clearMcccs($elementConstitutif);
            $entityManager->flush();
            $entityManager->refresh($elementConstitutif);
        }

        if ($disabled === true) {
            $folder = 'mccc-non-editable';
        } else {
            $folder = 'mccc';
        }

        switch ($request->query->get('type')) {
            case 'cc':
                if ($typeDiplome->getLibelleCourt() !== 'L') {

                    //seul cas particulier, pour les autres mêmes formulaires
                    return $this->render('typeDiplome/'.$folder.'/licence/_cc_autres_diplomes.html.twig', [
                        'mcccs' => $getElement->getMcccs($licenceTypeDiplome),
                        'typeEpreuves' => $typeEpreuves,
                        'elementConstitutif' => $elementConstitutif,
                    ]);
                }

                return $this->render('typeDiplome/'.$folder.'/licence/_cc.html.twig', [
                    'mcccs' => $getElement->getMcccs($licenceTypeDiplome),
                    'typeEpreuves' => $typeEpreuves,
                    'elementConstitutif' => $elementConstitutif,
                ]);

            case 'cci':
                return $this->render('typeDiplome/'.$folder.'/licence/_cci.html.twig', [
                    'mcccs' => $getElement->getMcccs($licenceTypeDiplome),
                    'typeEpreuves' => $typeEpreuves,
                    'elementConstitutif' => $elementConstitutif,
                ]);
            case 'cc_ct':
                return $this->render('typeDiplome/'.$folder.'/licence/_cc_ct.html.twig', [
                    'mcccs' => $getElement->getMcccs($licenceTypeDiplome),
                    'typeEpreuves' => $typeEpreuves,
                    'elementConstitutif' => $elementConstitutif,
                ]);
            case 'ct':
                return $this->render('typeDiplome/'.$folder.'/licence/_ct.html.twig', [
                    'mcccs' => $getElement->getMcccs($licenceTypeDiplome),
                    'typeEpreuves' => $typeEpreuves,
                    'elementConstitutif' => $elementConstitutif,
                ]);
        }

        return $this->render('typeDiplome/mccc/licence/_vide.html.twig', [
        ]);
    }



    #[Route('/type_diplome/save/mccc/hd/{ficheMatiere}', name: 'app_fiche_matiere_mccc_hors_diplome')]
    public function saveMcccHorsDiplome(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        TypeDiplomeRepository $typeDiplomeRepository,
        Request $request,
        FicheMatiere $ficheMatiere,
    ): Response {
        $typeDiplome = $typeDiplomeRepository->find(1); //todo: prendre le type de la fiche

        if ($typeDiplome === null) {
            throw new \Exception('Type de diplome non trouvé');
        }

        $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());

        if ($request->isMethod('POST')) {
            $typeD->saveMcccs($ficheMatiere, $request->request);
            return JsonReponse::success('MCCCs enregistrés');
        }

        return JsonReponse::error('Erreur lors de l\'enregistrement des MCCCs');
    }
}
