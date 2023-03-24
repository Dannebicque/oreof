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
use App\Entity\Ue;
use App\Form\EcStep4Type;
use App\Form\ElementConstitutifType;
use App\Repository\ElementConstitutifRepository;
use App\Repository\FicheMatiereRepository;
use App\Repository\LangueRepository;
use App\Repository\NatureUeEcRepository;
use App\Repository\TypeEpreuveRepository;
use App\Repository\UeRepository;
use App\Utils\JsonRequest;
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

    #[Route('/type-ec', name: 'app_element_constitutif_type_ec', methods: ['GET'])]
    public function typeEc(
        Request $request,
        FicheMatiereRepository $ficheMatiereRepository,
        NatureUeEcRepository $natureUeEcRepository
    ): Response {
        $natureEc = $natureUeEcRepository->find($request->query->get('choix'));
        if ($natureEc !== null) {
            if ($natureEc->isChoix() === true) {
                return $this->render('element_constitutif/_type_ec_matieres.html.twig', [
                    'matieres' => $ficheMatiereRepository->findAll()
                ]);
            }

            if ($natureEc->isLibre() === true) {
                return $this->render('element_constitutif/_type_ec_matieres_libre.html.twig', [
                ]);
            }

            return $this->render('element_constitutif/_type_ec_matiere.html.twig', [
                'matieres' => $ficheMatiereRepository->findAll()
            ]);
        }

        throw new RuntimeException('Nature EC non trouvée');
    }

    #[Route('/new/{ue}', name: 'app_element_constitutif_new', methods: ['GET', 'POST'])]
    public function new(
        EcOrdre $ecOrdre,
        Request $request,
        FicheMatiereRepository $ficheMatiereRepository,
        ElementConstitutifRepository $elementConstitutifRepository,
        Ue $ue
    ): Response {
        $elementConstitutif = new ElementConstitutif();
        $parcours = $ue->getSemestre()?->getSemestreParcours()->first()->getParcours();
        $elementConstitutif->setModaliteEnseignement($parcours?->getModalitesEnseignement());
        $elementConstitutif->setUe($ue);

        $form = $this->createForm(ElementConstitutifType::class, $elementConstitutif, [
            'action' => $this->generateUrl('app_element_constitutif_new', ['ue' => $ue->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($elementConstitutif->getNatureUeEc()?->isChoix() === false and $elementConstitutif->getNatureUeEc()?->isLibre() === false) {
                if (str_starts_with($request->request->get('ficheMatiere'), 'id_')) {
                    $ficheMatiere = $ficheMatiereRepository->find((int)str_replace(
                        'id_',
                        '',
                        $request->request->get('ficheMatiere')
                    ));
                } else {
                    $ficheMatiere = new FicheMatiere();
                    $ficheMatiere->setLibelle($request->request->get('ficheMatiereLibelle'));
                    $ficheMatiere->setParcours($parcours); //todo: ajouter le semestre
                    $ficheMatiereRepository->save($ficheMatiere, true);
                }
                $lastEc = $ecOrdre->getOrdreSuivant($ue);
                $elementConstitutif->setFicheMatiere($ficheMatiere);
                $elementConstitutif->setOrdre($lastEc);
                $elementConstitutif->genereCode();
                $elementConstitutifRepository->save($elementConstitutif, true);
            } elseif ($elementConstitutif->getNatureUeEc()?->isChoix() === true) {
                $lastEc = $ecOrdre->getOrdreSuivant($ue);
                $subOrdre = 1;
                //on récupère le champs matières, on découpe selon la ,. Si ca commence par "id_", on récupère la matière, sinon on créé la matière
                $matieres = explode(',', $request->request->get('matieres'));
                foreach ($matieres as $matiere) {
                    $ec = new ElementConstitutif();
                    $parcours = $ue->getSemestre()?->getSemestreParcours()->first()->getParcours();
                    //  $elementConstitutif->setParcours($ue->getSemestre()?->getSemestreParcours()->first()->getParcours());
                    $ec->setModaliteEnseignement($parcours?->getModalitesEnseignement());
                    $ec->setUe($ue);
                    $ec->setNatureUeEc($elementConstitutif->getNatureUeEc());

                    if (str_starts_with($matiere, 'id_')) {
                        $ficheMatiere = $ficheMatiereRepository->find((int)str_replace('id_', '', $matiere));
                    } else {
                        $ficheMatiere = new FicheMatiere();
                        $ficheMatiere->setLibelle(str_replace('ac_', '', $matiere));
                        $ficheMatiere->setParcours($parcours); //todo: ajouter le semestre
                        $ficheMatiereRepository->save($ficheMatiere, true);
                    }

                    $ec->setFicheMatiere($ficheMatiere);
                    $ec->setOrdre($lastEc);
                    $ec->setSubOrdre($subOrdre);
                    $ec->genereCode();
                    $subOrdre++;
                    $elementConstitutifRepository->save($ec, true);
                }
            } else {
                $lastEc = $ecOrdre->getOrdreSuivant($ue);
                $elementConstitutif->setTexteEcLibre($request->request->get('ficheMatiereLibre'));
                $elementConstitutif->setOrdre($lastEc);
                $elementConstitutif->setSubOrdre(0);
                $elementConstitutif->genereCode();
                $elementConstitutifRepository->save($elementConstitutif, true);
            }


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

//

            return $this->json(true);
        }

        return $this->render('element_constitutif/new.html.twig', [
            'element_constitutif' => $elementConstitutif,
            'form' => $form->createView(),
            'ue' => $ue,
            'parcours' => $parcours,
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[
        Route('/{id}', name: 'app_element_constitutif_show', methods: ['GET'])]
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
            )) || ($this->ecWorkflow->can($ficheMatiere, 'valider_ec') || $this->ecWorkflow->can(
                    $ficheMatiere,
                    'initialiser'
                )) || $this->isGranted('ROLE_ADMIN'));

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

    #[Route('/{id}', name: 'app_element_constitutif_delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        ElementConstitutif $elementConstitutif,
        ElementConstitutifRepository $elementConstitutifRepository
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $elementConstitutif->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf')
        )) {
            //todo: supprimer les autres enfants ???
            $elementConstitutifRepository->remove($elementConstitutif, true);

            //todo: remettre à jour tous les codes ???
            return $this->json(true);
        }

        return $this->json(false, 500);
    }
}
