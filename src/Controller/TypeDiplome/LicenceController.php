<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/TypeDiplome/LicenceController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 15/04/2023 22:19
 */

namespace App\Controller\TypeDiplome;

use App\Entity\ElementConstitutif;
use App\Repository\TypeDiplomeRepository;
use App\Repository\TypeEpreuveRepository;
use App\TypeDiplome\Source\LicenceTypeDiplome;
use App\TypeDiplome\TypeDiplomeRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LicenceController extends AbstractController
{
    #[Route('/type_diplome/change/licence/{elementConstitutif}', name: 'type_diplome_licence_change')]
    public function changeType(
        Request $request,
        LicenceTypeDiplome $licenceTypeDiplome,
        TypeDiplomeRepository $typeDiplomeRepository,
        TypeEpreuveRepository $typeEpreuveRepository,
        ElementConstitutif $elementConstitutif
    ) {
        $typeDiplome = $typeDiplomeRepository->findOneBy(['ModeleMcc' => LicenceTypeDiplome::class]);
        $typeEpreuves = $typeEpreuveRepository->findByTypeDiplome($typeDiplome);


        switch ($request->query->get('type')) {
            case 'cc':


                return $this->render('typeDiplome/mccc/licence/_cc.html.twig', [
                    'mcccs' => $licenceTypeDiplome->getMcccs($elementConstitutif),
                    'typeEpreuves' => $typeEpreuves,
                ]);
            case 'cci':
                return $this->render('typeDiplome/mccc/licence/_cci.html.twig', [
                    'mcccs' => $licenceTypeDiplome->getMcccs($elementConstitutif),
                    'typeEpreuves' => $typeEpreuves,
                ]);
            case 'cc_ct':
                return $this->render('typeDiplome/mccc/licence/_cc_ct.html.twig', [
                    'mcccs' => $licenceTypeDiplome->getMcccs($elementConstitutif),
                    'typeEpreuves' => $typeEpreuves,
                ]);
            case 'ct':
                return $this->render('typeDiplome/mccc/licence/_ct.html.twig', [
                    'mcccs' => $licenceTypeDiplome->getMcccs($elementConstitutif),
                    'typeEpreuves' => $typeEpreuves,
                ]);
        }
    }
}
