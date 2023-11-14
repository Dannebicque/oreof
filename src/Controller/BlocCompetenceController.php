<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/BlocCompetenceController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\Bcc;
use App\Entity\BlocCompetence;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Form\BlocCompetenceType;
use App\Repository\BlocCompetenceRepository;
use App\Repository\CompetenceRepository;
use App\TypeDiplome\Source\ButTypeDiplome;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\JsonRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/bloc/competence')]
class BlocCompetenceController extends AbstractController
{
//    #[Route('/liste/formation/{slug}', name: 'app_bloc_competence_liste_formation', methods: ['GET'])]
//    public function listeFormation(BlocCompetenceRepository $blocCompetenceRepository, Formation $formation): Response
//    {
//        return $this->render('bloc_competence/_liste.html.twig', [
//            'bloc_competences' => $blocCompetenceRepository->findByFormation($formation),
//        ]);
//    }

    #[Route('/liste/parcours/{parcours}', name: 'app_bloc_competence_liste_parcours', methods: ['GET'])]
    public function listeParcours(
        TypeDiplomeRegistry      $typeDiplomeRegistry,
        BlocCompetenceRepository $blocCompetenceRepository,
        ?Parcours                $parcours = null
    ): Response {
        if ($parcours === null) {
            return $this->render('bloc_competence/_liste.html.twig', [
                'bloc_competences' => $blocCompetenceRepository->findBy(['parcours' => null], ['ordre' => 'ASC']),
            ]);
        }
        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($parcours->getFormation()->getTypeDiplome()->getModeleMcc());

        if ($typeDiplome::SOURCE === ButTypeDiplome::SOURCE) {
            return $this->render('typeDiplome/but/_refCompetences.html.twig', [
                'competences' => $typeDiplome->getRefCompetences($parcours),
                'parcours' => $parcours,
            ]);
        }

        return $this->render('bloc_competence/_liste.html.twig', [
            'bloc_competences' => $blocCompetenceRepository->findByParcours($parcours),
            'parcours' => $parcours,
        ]);
    }

    public function afficheBUTReferentiel(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Parcours            $parcours
    ): Response {
        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($parcours->getFormation()->getTypeDiplome()->getModeleMcc());

        return $this->render('typeDiplome/but/_refCompetences.html.twig', [
            'competences' => $typeDiplome->getRefCompetences($parcours),
            'parcours' => $parcours,
        ]);
    }

    public function afficheBUTReferentielVersioning(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Parcours            $parcours
    ): Response
    {
        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($parcours->getFormation()->getTypeDiplome()->getModeleMcc());

        return $this->render('typeDiplome/but/_refCompetences.versioning.html.twig', [
            'competences' => $parcours->getFormation()->getButCompetences(),
            'parcours' => $parcours,
        ]);
    }


    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    #[Route('/liste/transverse/{parcours}', name: 'app_bloc_competence_liste_transverse', methods: ['GET', 'POST'])]
    public function listeTransverse(
        Request                  $request,
        BlocCompetenceRepository $blocCompetenceRepository,
        CompetenceRepository     $competenceRepository,
        Parcours                 $parcours
    ): Response {
        if ($request->isMethod('POST')) {
            $bccAdd = [];
            if ($request->request->has('competences')) {
                $competences = $request->request->all()['competences'];
                foreach ($competences as $c) {
                    $b = $competenceRepository->find($c);
                    if ($b !== null) {
                        $bc = $b->getBlocCompetence();
                        if ($bc !== null) {
                            if (!array_key_exists($bc->getId(), $bccAdd)) {
                                $bc = $b->getBlocCompetence();
                                if ($bc !== null) {
                                    $bcc = clone $bc;
                                    $ordre = $blocCompetenceRepository->getMaxOrdreParcours($parcours);
                                    $bcc->setOrdre($ordre + 1);
                                    $bcc->setParcours($parcours);
                                    $bcc->genereCode();
                                    $blocCompetenceRepository->save($bcc, true);
                                    $bccAdd[$bc->getId()] = $bcc;
                                }
                            }
                            $cCopie = clone $b;
                            $cCopie->setBlocCompetence($bccAdd[$bc->getId()]);
                            $ordre = $competenceRepository->getMaxOrdreBlocCompetence($bccAdd[$bc->getId()]);
                            $cCopie->setOrdre($ordre + 1);
                            $cCopie->genereCode();
                            $competenceRepository->save($cCopie, true);
                        }
                    }
                }
            }

            if ($request->request->has('bccs')) {
                $bccs = $request->request->all()['bccs'];
                foreach ($bccs as $bcc) {
                    if (!array_key_exists($bcc, $bccAdd)) {
                        $b = $blocCompetenceRepository->find($bcc);
                        if ($b !== null) {
                            $bcc = clone $b;
                            $ordre = $blocCompetenceRepository->getMaxOrdreParcours($parcours);
                            $bcc->setOrdre($ordre + 1);
                            $bcc->setParcours($parcours);
                            $bcc->genereCode();
                            $blocCompetenceRepository->save($bcc, true);
                        }
                    }
                }
            }

            return $this->json(true);
        }


        return $this->render('bloc_competence/_listeTransverse.html.twig', [
            'bccs' => $blocCompetenceRepository->findBy(['parcours' => null]),
            'parcours' => $parcours,
        ]);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    #[
        Route('/new/parcours/{parcours}', name: 'app_bloc_competence_new_parcours', methods: ['GET', 'POST'])]
    public function newParcours(
        Request                  $request,
        BlocCompetenceRepository $blocCompetenceRepository,
        CompetenceRepository     $competenceRepository,
        ?Parcours                $parcours = null
    ): Response {
        $blocCompetence = new BlocCompetence();
        $blocCompetence->setParcours($parcours);
        $form = $this->createForm(
            BlocCompetenceType::class,
            $blocCompetence,
            [
                'action' => $this->generateUrl(
                    'app_bloc_competence_new_parcours',
                    ['parcours' => $parcours?->getId(), 'ordre' => $request->query->get('ordre')]
                ),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->query->get('ordre') === null) {
                //pas d'ordre donc on ajoute à la fin
                $ordre = $blocCompetenceRepository->getMaxOrdreParcours($parcours);
                $blocCompetence->setOrdre($ordre + 1);
            } else {
                //on décale les autres compétences
                $bccs = $blocCompetenceRepository->decaleCompetence(
                    (int)$request->query->get('ordre'),
                    $parcours,
                );

                // décaler les blocs de compétences et recalculer les codes
                foreach ($bccs as $bcc) {
                    $bcc->setOrdre($bcc->getOrdre() + 1);
                    $bcc->genereCode();
                    $blocCompetenceRepository->save($bcc, true);
                    foreach ($bcc->getCompetences() as $competence) {
                        $competence->genereCode();
                        $competenceRepository->save($competence, true);
                    }
                }

                $blocCompetence->setOrdre((int)$request->query->get('ordre'));
            }

            $blocCompetence->genereCode();
            $blocCompetenceRepository->save($blocCompetence, true);

            return $this->json(true);
        }

        return $this->render('bloc_competence/new.html.twig', [
            'bloc_competence' => $blocCompetence,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_bloc_competence_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request                  $request,
        BlocCompetence           $blocCompetence,
        BlocCompetenceRepository $blocCompetenceRepository
    ): Response {
        $form = $this->createForm(
            BlocCompetenceType::class,
            $blocCompetence,
            [
                'action' => $this->generateUrl('app_bloc_competence_edit', ['id' => $blocCompetence->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $blocCompetenceRepository->save($blocCompetence, true);

            return $this->json(true);
        }

        return $this->render('bloc_competence/new.html.twig', [
            'bloc_competence' => $blocCompetence,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    #[Route('/{id}/duplicate', name: 'app_bloc_competence_duplicate', methods: ['GET'])]
    public function duplicate(
        BlocCompetenceRepository $blocCompetenceRepository,
        BlocCompetence           $blocCompetence
    ): Response {
        $blocCompetenceNew = clone $blocCompetence;
        $blocCompetenceNew->setLibelle($blocCompetence->getLibelle() . ' - Copie');

        if ($blocCompetence->getFormation()) {
            $ordre = $blocCompetenceRepository->getMaxOrdre($blocCompetence->getFormation());
        } else {
            $ordre = $blocCompetenceRepository->getMaxOrdreParcours($blocCompetence->getParcours());
        }

        $blocCompetenceNew->setOrdre($ordre + 1);
        $blocCompetenceNew->genereCode();

        $blocCompetenceRepository->save($blocCompetenceNew, true);

        return $this->json(true);
    }

    #[Route('/{id}/deplacer/{sens}', name: 'app_bloc_competence_deplacer', methods: ['GET'])]
    public function deplacer(
        Bcc            $bcc,
        BlocCompetence $blocCompetence,
        string         $sens
    ): Response {
        $bcc->deplacerBlocCompetence($blocCompetence, $sens);

        return $this->json(true);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/{id}', name: 'app_bloc_competence_delete', methods: ['DELETE'])]
    public function delete(
        Bcc                      $bcc,
        Request                  $request,
        BlocCompetence           $blocCompetence,
        BlocCompetenceRepository $blocCompetenceRepository
    ): Response {
        //todo: tester s'il y a des compétences dans le bloc
        if ($this->isCsrfTokenValid(
            'delete' . $blocCompetence->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf')
        )) {
            $parc = $blocCompetence->getParcours();
            $blocCompetenceRepository->remove($blocCompetence, true);
            $bcc->renumeroteBlocCompetence($parc);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
