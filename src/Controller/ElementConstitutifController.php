<?php

namespace App\Controller;

use App\Classes\EcOrdre;
use App\Entity\EcUe;
use App\Entity\ElementConstitutif;
use App\Entity\Parcours;
use App\Entity\Ue;
use App\Enums\EtatRemplissageEnum;
use App\Form\EcStep4Type;
use App\Form\ElementConstitutifType;
use App\Repository\EcUeRepository;
use App\Repository\ElementConstitutifRepository;
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
    public function index(ElementConstitutifRepository $elementConstitutifRepository): Response
    {
        return $this->render('element_constitutif/index.html.twig', [
            'element_constitutifs' => $elementConstitutifRepository->findAll(),
        ]);
    }

    #[Route('/new/{ue}', name: 'app_element_constitutif_new', methods: ['GET', 'POST'])]
    public function new(
        EcOrdre $ecOrdre,
        EcUeRepository $ecUeRepository,
        LangueRepository $langueRepository,
        Request $request,
        ElementConstitutifRepository $elementConstitutifRepository,
        Ue $ue
    ): Response {
        $elementConstitutif = new ElementConstitutif();

        $form = $this->createForm(ElementConstitutifType::class, $elementConstitutif, [
            'action' => $this->generateUrl('app_element_constitutif_new', ['ue' => $ue->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lastEc = $ecOrdre->getOrdreSuivant($ue);
            $elementConstitutif->setCode('EC ' . $lastEc);
            $elementConstitutif->setOrdre($lastEc);
            $ueEc = new EcUe($ue, $elementConstitutif);
            $ecUeRepository->save($ueEc, true);


            $langueFr = $langueRepository->findOneBy(['codeIso' => 'fr']);
            if ($langueFr !== null) {
                $elementConstitutif->addLangueDispense($langueFr);
                $langueFr->addElementConstitutif($elementConstitutif);
                $elementConstitutif->addLangueSupport($langueFr);
                $langueFr->addLanguesSupportsEc($elementConstitutif);
            }

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
        TypeDiplomeRegistry $typeDiplomeRegistry,
        ElementConstitutif $elementConstitutif
    ): Response {
        $formation = $elementConstitutif->getParcours()->getFormation();
        if ($formation === null) {
            throw new RuntimeException('Formation non trouvée');
        }

        $bccs = [];
        foreach ($elementConstitutif->getCompetences() as $competence) {
            if (!array_key_exists($competence->getBlocCompetence()?->getId(), $bccs)) {
                $bccs[$competence->getBlocCompetence()?->getId()]['bcc'] = $competence->getBlocCompetence();
                $bccs[$competence->getBlocCompetence()?->getId()]['competences'] = [];
            }
            $bccs[$competence->getBlocCompetence()?->getId()]['competences'][] = $competence;
        }

        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());

        return $this->render('element_constitutif/show.html.twig', [
            'elementConstitutif' => $elementConstitutif,
            'template' => $typeDiplome::TEMPLATE,
            'formation' => $formation,
            'typeDiplome' => $typeDiplome,
            'bccs' => $bccs
        ]);
    }

    #[Route('/{id}/edit/{parcours}', name: 'app_element_constitutif_edit', methods: ['GET', 'POST'])]
    public function edit(
        ElementConstitutif $elementConstitutif,
        Parcours $parcours
    ): Response {

        //(is_granted('ROLE_FORMATION_EDIT_MY', ec.parcours.formation) or is_granted
        //                        ('ROLE_EC_EDIT_MY', ec)) and  workflow_can(ec,
        //                        'valider_ec')

        $access = ($this->isGranted('ROLE_EC_EDIT_MY',
                    $elementConstitutif) || $this->isGranted('ROLE_FORMATION_EDIT_MY',
                    $parcours->getFormation())) && $this->ecWorkflow->can($elementConstitutif, 'valider_ec');

        if (!$access) {
            throw new AccessDeniedException();
        }


        return $this->render('element_constitutif/edit.html.twig', [
            'ec' => $elementConstitutif,
            'parcours' => $parcours,
            'onglets' => $elementConstitutif->etatRemplissageOnglets(),
        ]);
    }

    #[Route('/{id}/structure-ec', name: 'app_element_constitutif_structure', methods: ['GET', 'POST'])]
    public function structureEc(
        Request $request,
        ElementConstitutifRepository $elementConstitutifRepository,
        ElementConstitutif $elementConstitutif
    ): Response {
        if ($this->isGranted('ROLE_FORMATION_EDIT_MY',
            $elementConstitutif->getParcours()->getFormation())) { //todo: ajouter le workflow...
            $form = $this->createForm(EcStep4Type::class, $elementConstitutif, [
                'isModal' => true,
                'action' => $this->generateUrl('app_element_constitutif_structure',
                    ['id' => $elementConstitutif->getId()]),
            ]);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $elementConstitutifRepository->save($elementConstitutif, true);

                return $this->json(true);
            }

            return $this->render('element_constitutif/_structureEcModal.html.twig', [
                'ec' => $elementConstitutif,
                'form' => $form->createView(),
            ]);
        }

        return $this->render('element_constitutif/_structureEcNonEditable.html.twig', [
            'ec' => $elementConstitutif,
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{id}/mccc-ec', name: 'app_element_constitutif_mccc', methods: ['GET', 'POST'])]
    public function mcccEc(
        TypeEpreuveRepository $typeEpreuveRepository,
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Request $request,
        ElementConstitutifRepository $elementConstitutifRepository,
        ElementConstitutif $elementConstitutif
    ): Response {
        $formation = $elementConstitutif->getParcours()->getFormation();
        if ($formation === null) {
            throw new RuntimeException('Formation non trouvée');
        }
        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());
        if ($this->isGranted('ROLE_FORMATION_EDIT_MY',
            $elementConstitutif->getParcours()->getFormation())) { //todo: ajouter le workflow...


            if ($elementConstitutif->getMcccs()->count() === 0) {
                $typeDiplome->initMcccs($elementConstitutif);
            }

            if ($request->isMethod('POST')) {
                $typeDiplome->saveMcccs($elementConstitutif, $request->request);
                $elementConstitutifRepository->save($elementConstitutif, true);

                return $this->json(true);
            }

            return $this->render('element_constitutif/_mcccEcModal.html.twig', [
                'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
                'ec' => $elementConstitutif,
                'templateForm' => $typeDiplome::TEMPLATE_FORM_MCCC,
                'mcccs' => $typeDiplome->getMcccs($elementConstitutif),
                'wizard' => false
            ]);

        }

        return $this->render('element_constitutif/_mcccEcNonEditable.html.twig', [
            'ec' => $elementConstitutif,
            'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
            'templateForm' => $typeDiplome::TEMPLATE_FORM_MCCC,
            'mcccs' => $typeDiplome->getMcccs($elementConstitutif),
        ]);

    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    public function displayMcccEc(
        TypeEpreuveRepository $typeEpreuveRepository,
        TypeDiplomeRegistry $typeDiplomeRegistry,
        ElementConstitutif $elementConstitutif
    ): Response {
        $formation = $elementConstitutif->getParcours()->getFormation();
        if ($formation === null) {
            throw new RuntimeException('Formation non trouvée');
        }
        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());

        return $this->render('element_constitutif/_mcccEcNonEditable.html.twig', [
            'ec' => $elementConstitutif,
            'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
            'templateForm' => $typeDiplome::TEMPLATE_FORM_MCCC,
            'mcccs' => $typeDiplome->getMcccs($elementConstitutif),
        ]);
    }

    #[Route('/{id}', name: 'app_element_constitutif_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        ElementConstitutif $elementConstitutif,
        ElementConstitutifRepository $elementConstitutifRepository
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $elementConstitutif->getId(), $request->request->get('_token'))) {
            $elementConstitutifRepository->remove($elementConstitutif, true);
        }

        return $this->redirectToRoute('app_element_constitutif_index', [], Response::HTTP_SEE_OTHER);
    }
}
