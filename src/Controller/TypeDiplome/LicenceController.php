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
use App\Repository\ElementConstitutifRepository;
use App\Repository\TypeDiplomeRepository;
use App\Repository\TypeEpreuveRepository;
use App\TypeDiplome\Source\LicenceTypeDiplome;
use App\TypeDiplome\TypeDiplomeRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LicenceController extends AbstractController
{
    #[Route('/type_diplome/change/licence/{elementConstitutif}', name: 'type_diplome_licence_change')]
    public function changeType(
        Request $request,
        EntityManagerInterface $entityManager,
        LicenceTypeDiplome $licenceTypeDiplome,
        TypeDiplomeRegistry $typeDiplomeRegistry,
        TypeDiplomeRepository $typeDiplomeRepository,
        TypeEpreuveRepository $typeEpreuveRepository,
        ElementConstitutifRepository $elementConstitutifRepository,
        ElementConstitutif $elementConstitutif
    ) {
        $typeDiplome = $typeDiplomeRepository->findOneBy(['ModeleMcc' => LicenceTypeDiplome::class]);

        if ($typeDiplome === null) {
            throw new \Exception('Type de diplome non trouvé');
        }

        $typeEpreuves = $typeEpreuveRepository->findByTypeDiplome($typeDiplome);

        if ($request->query->get('type') !== $elementConstitutif->getTypeMccc()) {
            $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());
            $elementConstitutif->setTypeMccc($request->query->get('type'));
            $elementConstitutifRepository->save($elementConstitutif, true);
            $typeD->clearMcccs($elementConstitutif);
            $typeD->initMcccs($elementConstitutif);
            $entityManager->flush();
            $entityManager->refresh($elementConstitutif);

        }

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

        return $this->render('typeDiplome/mccc/licence/_vide.html.twig', [
        ]);
    }
}
