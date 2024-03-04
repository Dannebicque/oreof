<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FicheMatiereController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Classes\verif\FicheMatiereState;
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\FicheMatiereVersioning;
use App\Entity\Parcours;
use App\Form\FicheMatiereType;
use App\Repository\ElementConstitutifRepository;
use App\Repository\FicheMatiereRepository;
use App\Repository\LangueRepository;
use App\Repository\UeRepository;
use App\Utils\JsonRequest;
use DateTimeImmutable;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Jfcherng\Diff\DiffHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route('/fiche/matiere')]
class FicheMatiereController extends AbstractController
{
    #[Route('/new', name: 'app_fiche_matiere_new', methods: ['GET', 'POST'])]
    public function new(
        UeRepository $ueRepository,
        EntityManagerInterface $entityManager,
        LangueRepository $langueRepository,
        Request $request,
    ): Response {
        $ficheMatiere = new FicheMatiere();
        if ($request->query->has('ue')) {
            $ue = $ueRepository->find($request->query->get('ue'));
            $ficheMatiere->setParcours($ue->getSemestre()?->getSemestreParcours()->first()->getParcours());
        } else {
            $ficheMatiere->setHorsDiplome(true);
        }

        //todo: initialiser les modalités par rapport au parcours

        $form = $this->createForm(FicheMatiereType::class, $ficheMatiere, [
            'action' => $this->generateUrl('app_fiche_matiere_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $langueFr = $langueRepository->findOneBy(['codeIso' => 'fr']);
            if ($langueFr !== null) {
                $ficheMatiere->addLangueDispense($langueFr);
                $langueFr->addFicheMatiere($ficheMatiere);
                $ficheMatiere->addLangueSupport($langueFr);
                $langueFr->addLanguesSupportsFicheMatiere($ficheMatiere);
            }

            $entityManager->persist($ficheMatiere);
            $entityManager->flush();

            return $this->json(true);
        }

        return $this->render('fiche_matiere/new.html.twig', [
            'fiche_matiere' => $ficheMatiere,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{slug}', name: 'app_fiche_matiere_show', methods: ['GET'])]
    public function show(
        FicheMatiere $ficheMatiere
    ): Response {
        $formation = $ficheMatiere->getParcours()?->getFormation();
//        if ($formation === null) {
//            throw new RuntimeException('Formation non trouvée');
//        }

        $bccs = [];
        foreach ($ficheMatiere->getCompetences() as $competence) {
            if (!array_key_exists($competence->getBlocCompetence()?->getId(), $bccs)) {
                $bccs[$competence->getBlocCompetence()?->getId()]['bcc'] = $competence->getBlocCompetence();
                $bccs[$competence->getBlocCompetence()?->getId()]['competences'] = [];
            }
            $bccs[$competence->getBlocCompetence()?->getId()]['competences'][] = $competence;
        }

        if ($formation !== null) {
            $typeDiplome = $formation->getTypeDiplome();
        } else {
            $typeDiplome = null;
        }

        return $this->render('fiche_matiere/show.html.twig', [
            'ficheMatiere' => $ficheMatiere,
            'formation' => $formation,
            'typeDiplome' => $typeDiplome,
            'bccs' => $bccs
        ]);
    }

    #[Route('/{elementConstitutif}/show-parcours', name: 'app_fiche_matiere_detail_parcours', methods: ['GET'])]
    public function showParcours(
        ElementConstitutif $elementConstitutif
    ): Response {

        if ($elementConstitutif->isFicheFromParcours() === true) {
            $competences = $elementConstitutif->getFicheMatiere()->getCompetences();
        } else {
            $competences = $elementConstitutif->getCompetences();
        }

        $bccs = [];
        foreach ($competences as $competence) {
            if (!array_key_exists($competence->getBlocCompetence()?->getId(), $bccs)) {
                $bccs[$competence->getBlocCompetence()?->getId()]['bcc'] = $competence->getBlocCompetence();
                $bccs[$competence->getBlocCompetence()?->getId()]['competences'] = [];
            }
            $bccs[$competence->getBlocCompetence()?->getId()]['competences'][] = $competence;
        }


        return $this->render('fiche_matiere/_showParcours.html.twig', [
            'ficheMatiere' => $elementConstitutif->getFicheMatiere(),
            'bccs' => $bccs
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_fiche_matiere_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        FicheMatiere $ficheMatiere,
        FicheMatiereState $ficheMatiereState,
    ): Response {
        if (!$this->isGranted('CAN_EC_EDIT_MY', $ficheMatiere)) {
            return $this->redirectToRoute('app_fiche_matiere_show', ['slug' => $ficheMatiere->getSlug()]);
        }

        $ficheMatiereState->setFicheMatiere($ficheMatiere);

        $referer = $request->headers->get('referer');

        if ($referer === null || false === str_contains($referer, 'parcours')) {
            $source = 'liste';
        } else {
            $source = 'parcours';
            $link = $referer.'?step=4';
        }

        return $this->render('fiche_matiere/edit.html.twig', [
            'fiche_matiere' => $ficheMatiere,
            'ficheMatiereState' => $ficheMatiereState,
            'source' => $source,
            'link' => $link ?? null,
        ]);
    }

    #[Route('/{slug}/dupliquer', name: 'app_fiche_matiere_dupliquer', methods: ['GET'])]
    public function dupliquer(
        FicheMatiere $ficheMatiere,
        ElementConstitutifRepository $elementConstitutifRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $newFicheMatiere = clone $ficheMatiere;
        $newFicheMatiere->setLibelle($ficheMatiere->getLibelle() . '-copie');
        $newFicheMatiere->setSlug(null);
        $entityManager->persist($newFicheMatiere);
        $entityManager->flush();

        foreach($ficheMatiere->getFicheMatiereParcours() as $parcours) {
            //on duplique les parcours de mutualisation
            $newFicheMatiereParcours = clone $parcours;
            $newFicheMatiereParcours->setFicheMatiere($newFicheMatiere);
            $entityManager->persist($newFicheMatiereParcours);
            $entityManager->flush();
        }

        return $this->json(true);
    }

    #[Route('/{slug}', name: 'app_fiche_matiere_delete', methods: ['DELETE'])]
    public function delete(
        EntityManagerInterface $entityManager,
        Request $request,
        FicheMatiere $ficheMatiere,
        FicheMatiereRepository $ficheMatiereRepository
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $ficheMatiere->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf')
        )) {

            if ($ficheMatiere->getElementConstitutifs()->count() > 0) {
                return JsonReponse::error('Impossible de supprimer la fiche matière car elle est utilisée par au moins un élément constitutif.');
            }

            if ($ficheMatiere->getFicheMatiereParcours()->count() > 0) {
                return JsonReponse::error('Impossible de supprimer la fiche matière car elle est potentiellement mutualisée avec d\'autres parcours.');
            }

            foreach ($ficheMatiere->getMcccs() as $mccc) {
                $ficheMatiere->removeMccc($mccc);
                $entityManager->remove($mccc);
            }

            $ficheMatiereRepository->remove($ficheMatiere, true);

            return JsonReponse::success('La fiche matière a bien été supprimée.');
        }

        return $this->json(false);
    }

    #[Route('/{ec}/{parcours}/maquette_iframe', name: 'app_fiche_matiere_maquette_iframe')]
    public function getMaquetteIframe(ElementConstitutif $ec, Parcours $parcours) : Response {
        $ficheMatiere = $ec->getFicheMatiere();

        return $this->render('fiche_matiere/maquette_iframe.html.twig', [
            'fiche_matiere' => $ficheMatiere,
            'typeDiplome' => $ficheMatiere->getParcours()?->getFormation()?->getTypeDiplome(),
            'formation' => $ficheMatiere->getParcours()?->getFormation(),
            'maquetteOrigineURL' => $parcours ? $this->generateUrl('app_parcours_maquette_iframe', ['parcours' => $parcours->getId()]) : "#",
            'element_constitutif' => $ec
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{slug}/versioning/save', name: 'app_fiche_matiere_versioning_save', methods: ['GET'])]
    public function saveFicheMatiereIntoJson(
        FicheMatiere $ficheMatiere,
        EntityManagerInterface $entityManager,
        Filesystem $fileSystem
    ){
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $serializer = new Serializer(
            [new ObjectNormalizer($classMetadataFactory, propertyTypeExtractor: new ReflectionExtractor())],
            [new JsonEncoder()]
        );
        try{
            // Date / Heure
            $now = new DateTimeImmutable('now');
            $dateHeure = $now->format('d-m-Y_H-i-s');
            // Objet BD fiche matiere versioning
            $ficheMatiereVersioning = new FicheMatiereVersioning();
            $ficheMatiereVersioning->setFicheMatiere($ficheMatiere);
            $ficheMatiereVersioning->setVersionTimestamp($now);
            $ficheMatiereVersioning->setSlug($ficheMatiere->getSlug());
            // Fichier Json
            $ficheMatiereFileName = "fiche-matiere-{$ficheMatiere->getId()}-{$dateHeure}";
            $ficheMatiereVersioning->setFilename($ficheMatiereFileName);
            // Serialization
            $ficheMatiereJson = $serializer->serialize($ficheMatiere, 'json', [
                AbstractObjectNormalizer::GROUPS => ['fiche_matiere_versioning', 'fiche_matiere_versioning_ec_parcours'],
                'circular_reference_limit' => 2,
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
                DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'
            ]);
            // Enregistrement du fichier
            $fileSystem->appendToFile(
                __DIR__ . "/../../versioning_json/fiche-matiere/{$ficheMatiereVersioning->getSlug()}/{$ficheMatiereFileName}.json",
                $ficheMatiereJson
            );
            // Enregistrement en BD
            $entityManager->persist($ficheMatiereVersioning);
            $entityManager->flush();
            // Ajout dans les logs
            /**
             * @var User $user
             */
            $user = $this->getUser();
            $successLogTxt = "[{$dateHeure}] La fiche matière {$ficheMatiere->getSlug()} a été versionnée avec succès. ";
            $successLogTxt .= "Utilisateur : {$user->getPrenom()} {$user->getNom()} ({$user->getUsername()})\n";
            $fileSystem->appendToFile(__DIR__ . "/../../versioning_json/success_log/save_fiche_matiere_success.log", $successLogTxt);
            // Redirection
            $this->addFlash('toast', [
                'type' => 'success',
                'text' => 'La fiche matière a bien été sauvegardée.',
            ]);
            return $this->redirectToRoute('app_fiche_matiere_show', ['slug' => $ficheMatiere->getSlug()]);
        }catch(\Exception $e){
            // Log error
            $logTxt = "[{$dateHeure}] Le versioning de la fiche matière : "
                . "{$ficheMatiere->getSlug()} - ID : {$ficheMatiere->getId()}"
                . "- a rencontré une erreur.\n{$e->getMessage()}\n";
            $fileSystem->appendToFile(__DIR__ . "/../../versioning_json/error_log/save_fiche_matiere_error.log", $logTxt);

            $this->addFlash('toast', [
                'type' => 'error',
                'text' => "Une erreur est survenue lors de la sauvegarde."
            ]);
            return $this->redirectToRoute('app_fiche_matiere_show', ['slug' => $ficheMatiere->getSlug()]);
        }
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/versioning/view', name: 'app_fiche_matiere_versioning_view')]
    public function getJsonVersion(
        FicheMatiereVersioning $ficheMatiereVersioning
    ){
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $serializer = new Serializer(
            [
                new ArrayDenormalizer(),
                new ObjectNormalizer($classMetadataFactory, propertyTypeExtractor: new ReflectionExtractor())
            ],
            [new JsonEncoder()]
        );
        $ficheMatiereJson = file_get_contents(
            __DIR__ . "/../../versioning_json/fiche-matiere/"
            . "{$ficheMatiereVersioning->getSlug()}/"
            . "{$ficheMatiereVersioning->getFilename()}.json"
        );
        $ficheMatiere = $serializer->deserialize($ficheMatiereJson, FicheMatiere::class, 'json');
        $dateVersion = $ficheMatiereVersioning->getVersionTimestamp()->format('d-m-Y à H:i');

        $bccs = [];
        foreach ($ficheMatiere->getCompetences() as $competence) {
            if (!array_key_exists($competence->getBlocCompetence()?->getId(), $bccs)) {
                $bccs[$competence->getBlocCompetence()?->getId()]['bcc'] = $competence->getBlocCompetence();
                $bccs[$competence->getBlocCompetence()?->getId()]['competences'] = [];
            }
            $bccs[$competence->getBlocCompetence()?->getId()]['competences'][] = $competence;
        }

        $cssDiff = DiffHelper::getStyleSheet();

        $rendererName = 'Inline';
        $differOptions = [
            'context' => 1,
            'ignoreWhitespace' => true,
            'ignoreLineEnding' => true,
        ];
        $rendererOptions = [
            'detailLevel' => 'char',
            'lineNumbers' => false,
            'showHeader' => false,
            'separateBlock' => false,
        ];

        return $this->render('fiche_matiere/show.versioning.html.twig', [
            'ficheMatiere' => $ficheMatiere,
            'formation' => $ficheMatiere->getParcours()->getFormation(),
            'typeDiplome' => $ficheMatiere->getParcours()->getFormation()->getTypeDiplome(),
            'bccs' => $bccs,
            'dateHeure' => $dateVersion,
            'stringDifferences' => [
                'descriptionEnseignement' => html_entity_decode(DiffHelper::calculate(
                    $ficheMatiere->getDescription(),
                    $ficheMatiereVersioning->getFicheMatiere()->getDescription(),
                    $rendererName,
                    $differOptions,
                    $rendererOptions
                )),
                'objectifsEnseignement' => html_entity_decode(DiffHelper::calculate(
                    $ficheMatiere->getObjectifs(),
                    $ficheMatiereVersioning->getFicheMatiere()->getObjectifs(),
                    $rendererName,
                    $differOptions,
                    $rendererOptions
                ))
            ],
            'cssDiff' => $cssDiff
        ]);
    }

}
