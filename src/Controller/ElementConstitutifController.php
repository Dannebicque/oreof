<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FicheMatiereController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\Bcc;
use App\Classes\EcOrdre;
use App\Entity\BlocCompetence;
use App\Entity\EcUe;
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use App\Entity\Ue;
use App\Enums\EtatRemplissageEnum;
use App\Form\EcStep4Type;
use App\Form\ElementConstitutifType;
use App\Repository\EcUeRepository;
use App\Repository\ElementConstitutifRepository;
use App\Repository\FicheMatiereRepository;
use App\Repository\LangueRepository;
use App\Repository\TypeEpreuveRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/element/constitutif')]
class ElementConstitutifController extends AbstractController
{
    public function __construct(private readonly WorkflowInterface $ecWorkflow)
    {
    }

    #[Route('/', name: 'app_element_constitutif_index', methods: ['GET'])]
    public function index(FicheMatiereRepository $ficheMatiereRepository): Response
    {
        return $this->render('element_constitutif/index.html.twig', [
            'element_constitutifs' => $ficheMatiereRepository->findAll(),
        ]);
    }

    #[Route('/new/{ue}', name: 'app_element_constitutif_new', methods: ['GET', 'POST'])]
    public function new(
        EcOrdre $ecOrdre,
        EcUeRepository $ecUeRepository,
        Request $request,
        ElementConstitutifRepository $elementConstitutifRepository,
        Ue $ue
    ): Response {
        $elementConstitutif = new ElementConstitutif();
        $elementConstitutif->setParcours($ue->getSemestre()?->getSemestreParcours()->first()->getParcours());
        $elementConstitutif->setModaliteEnseignement($ue->getSemestre()?->getSemestreParcours()->first()->getParcours()?->getModalitesEnseignement());

        $form = $this->createForm(ElementConstitutifType::class, $elementConstitutif, [
            'action' => $this->generateUrl('app_element_constitutif_new', ['ue' => $ue->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lastEc = $ecOrdre->getOrdreSuivant($ue);
            $elementConstitutif->setModaliteEnseignement($ue->getSemestre()?->getSemestreParcours()->first()->getParcours()?->getModalitesEnseignement());
            $elementConstitutif->setOrdre($lastEc);
            $elementConstitutif->genereCode();
            $ueEc = new EcUe($ue, $elementConstitutif);
            $ecUeRepository->save($ueEc, true);

            $formation = $ue->getSemestre()?->getSemestreParcours()->first()->getParcours()?->getFormation();
            if ($formation === null) {
                throw new RuntimeException('Formation non trouvée');
            }
//            $typeDiplome = $formation->getTypeDiplome();
//            $typeDiplome->initMcccs($ficheMatiere); //todo: revoir pour appeler le type de diplome

//            $langueFr = $langueRepository->findOneBy(['codeIso' => 'fr']);
//            if ($langueFr !== null) {
//                $ficheMatiere->addLangueDispense($langueFr);
//                $langueFr->addFicheMatiere($ficheMatiere);
//                $ficheMatiere->addLangueSupport($langueFr);
//                $langueFr->addLanguesSupportsEc($ficheMatiere);
//            }

            $elementConstitutifRepository->save($elementConstitutif, true);

            return $this->json(true);
        }

        return $this->render('element_constitutif/new.html.twig', [
            'element_constitutif' => $elementConstitutif,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{id}', name: 'app_element_constitutif_show', methods: ['GET'])]
    public function show(
        FicheMatiere $ficheMatiere
    ): Response {
        $formation = $ficheMatiere->getParcours()->getFormation();
        if ($formation === null) {
            throw new RuntimeException('Formation non trouvée');
        }

        $bccs = [];
        foreach ($ficheMatiere->getCompetences() as $competence) {
            if (!array_key_exists($competence->getBlocCompetence()?->getId(), $bccs)) {
                $bccs[$competence->getBlocCompetence()?->getId()]['bcc'] = $competence->getBlocCompetence();
                $bccs[$competence->getBlocCompetence()?->getId()]['competences'] = [];
            }
            $bccs[$competence->getBlocCompetence()?->getId()]['competences'][] = $competence;
        }

        $typeDiplome = $formation->getTypeDiplome();

        return $this->render('element_constitutif/show.html.twig', [
            'ficheMatiere' => $ficheMatiere,
            'template' => $typeDiplome::TEMPLATE,//todo: revoir pour appeler le template global ?
            'formation' => $formation,
            'typeDiplome' => $typeDiplome,
            'bccs' => $bccs
        ]);
    }

    #[Route('/{id}/edit/{parcours}', name: 'app_element_constitutif_edit', methods: ['GET', 'POST'])]
    public function edit(
        FicheMatiere $ficheMatiere,
        Parcours $parcours
    ): Response {
        //(is_granted('ROLE_FORMATION_EDIT_MY', ec.parcours.formation) or is_granted
        //                        ('ROLE_EC_EDIT_MY', ec)) and  workflow_can(ec,
        //                        'valider_ec')

        $access = (($this->isGranted(
            'ROLE_EC_EDIT_MY',
            $ficheMatiere
        ) && $this->ecWorkflow->can($ficheMatiere, 'valider_ec')) || ($this->isGranted(
                'ROLE_FORMATION_EDIT_MY',
                $parcours->getFormation()
            )) || ($this->ecWorkflow->can($ficheMatiere, 'valider_ec') || $this->ecWorkflow->can($ficheMatiere, 'initialiser')) || $this->isGranted('ROLE_ADMIN'));

        if (!$access) {
            throw new AccessDeniedException();
        }


        return $this->render('element_constitutif/edit.html.twig', [
            'ec' => $ficheMatiere,
            'parcours' => $parcours,
            'onglets' => $ficheMatiere->etatRemplissageOnglets(),
        ]);
    }

    #[Route('/{id}/structure-ec', name: 'app_element_constitutif_structure', methods: ['GET', 'POST'])]
    public function structureEc(
        Request $request,
        FicheMatiereRepository $ficheMatiereRepository,
        ElementConstitutif $elementConstitutif
    ): Response {
//        if ($this->isGranted(
//            'ROLE_FORMATION_EDIT_MY',
//            $elementConstitutif->getParcours()->getFormation()
//        )) { //todo: ajouter le workflow...
            $form = $this->createForm(EcStep4Type::class, $elementConstitutif, [
                'isModal' => true,
                'action' => $this->generateUrl(
                    'app_element_constitutif_structure',
                    ['id' => $elementConstitutif->getId()]
                ),
            ]);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $ficheMatiereRepository->save($elementConstitutif, true);

                return $this->json(true);
            }

            return $this->render('element_constitutif/_structureEcModal.html.twig', [
                'ec' => $elementConstitutif,
                'form' => $form->createView(),
            ]);
       // }
//
//        return $this->render('element_constitutif/_structureEcNonEditable.html.twig', [
//            'ec' => $elementConstitutif,
//        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{id}/mccc-ec', name: 'app_element_constitutif_mccc', methods: ['GET', 'POST'])]
    public function mcccEc(
        TypeEpreuveRepository $typeEpreuveRepository,
        Request $request,
        FicheMatiereRepository $ficheMatiereRepository,
        ElementConstitutif $elementConstitutif
    ): Response {
        $formation = $elementConstitutif->getParcours()->getFormation();
        if ($formation === null) {
            throw new RuntimeException('Formation non trouvée');
        }
        $typeDiplome = $formation->getTypeDiplome();
        if ($this->isGranted(
            'ROLE_FORMATION_EDIT_MY',
            $elementConstitutif->getParcours()->getFormation()
        )) { //todo: ajouter le workflow...
            if ($elementConstitutif->getMcccs()->count() === 0) {
                $typeDiplome->initMcccs($elementConstitutif);//todo: appeler les mcc du bon diplôme
            }

            if ($request->isMethod('POST')) {
                $typeDiplome->saveMcccs($elementConstitutif, $request->request);//todo: appeler les mcc du bon diplôme
                $ficheMatiereRepository->save($elementConstitutif, true);

                return $this->json(true);
            }

            return $this->render('element_constitutif/_mcccEcModal.html.twig', [
                'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
                'ec' => $elementConstitutif,
                'templateForm' => $typeDiplome::TEMPLATE_FORM_MCCC, //todo: appeler les mcc du bon diplôme
                'mcccs' => $typeDiplome->getMcccs($elementConstitutif), //todo: appeler les mcc du bon diplôme
                'wizard' => false
            ]);
        }

        return $this->render('element_constitutif/_mcccEcNonEditable.html.twig', [
            'ec' => $ficheMatiere,
            'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
            'templateForm' => $typeDiplome::TEMPLATE_FORM_MCCC,
            'mcccs' => $typeDiplome->getMcccs($ficheMatiere),
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    public function displayMcccEc(
        TypeEpreuveRepository $typeEpreuveRepository,
        FicheMatiere $ficheMatiere
    ): Response {
        $formation = $ficheMatiere->getParcours()->getFormation();
        if ($formation === null) {
            throw new RuntimeException('Formation non trouvée');
        }
        $typeDiplome = $formation->getTypeDiplome();

        return $this->render('element_constitutif/_mcccEcNonEditable.html.twig', [
            'ec' => $ficheMatiere,
            'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
            'templateForm' => $typeDiplome::TEMPLATE_FORM_MCCC,//todo: appeler les mcc du bon diplôme
            'mcccs' => $typeDiplome->getMcccs($ficheMatiere),//todo: appeler les mcc du bon diplôme
        ]);
    }

    #[Route('/{id}/{ue}/deplacer/{sens}', name: 'app_element_constitutif_deplacer', methods: ['GET'])]
    public function deplacer(
        EcOrdre $ecOrdre,
        FicheMatiere $ficheMatiere,
        Ue $ue,
        string $sens
    ): Response {
        $ecOrdre->deplacerFicheMatiere($ficheMatiere, $sens, $ue);
        return $this->json(true);
    }

    #[Route('/{id}', name: 'app_element_constitutif_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        FicheMatiere $ficheMatiere,
        FicheMatiereRepository $ficheMatiereRepository
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $ficheMatiere->getId(), $request->request->get('_token'))) {
            $ficheMatiereRepository->remove($ficheMatiere, true);
        }

        return $this->redirectToRoute('app_element_constitutif_index', [], Response::HTTP_SEE_OTHER);
    }
}
