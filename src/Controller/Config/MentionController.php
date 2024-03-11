<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Config/MentionController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller\Config;

use App\Entity\Mention;
use App\Form\MentionType;
use App\Repository\DomaineRepository;
use App\Repository\MentionRepository;
use App\Repository\TypeDiplomeRepository;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/mention')]
class MentionController extends AbstractController
{
    public const TAB_CODE_PARCOURS = [

        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'Y',
        'Z',
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
    ];

    #[Route('/', name: 'app_mention_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('config/mention/index.html.twig');
    }

    #[Route('/codification-modal', name: 'app_mention_codification_modal', methods: ['GET'])]
    public function codificationModal(
        DomaineRepository     $domaineRepository,
        TypeDiplomeRepository $typeDiplomeRepository
    ): Response {
        return $this->render('config/mention/_codificationModal.html.twig', [
            'typeDiplomes' => $typeDiplomeRepository->findBy([], ['libelle' => 'ASC']),
            'domaines' => $domaineRepository->findBy([], ['libelle' => 'ASC']),

        ]);
    }

    #[Route('/codification', name: 'app_mention_codification', methods: ['POST'])]
    public function codification(
        Request               $request,
        DomaineRepository      $domaineRepository,
        TypeDiplomeRepository  $typeDiplomeRepository,
        MentionRepository      $mentionRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $checkTypeDiplome = $request->request->all()['typediplome'];
        $checkTypeDomaine = $request->request->all()['domaine'];

        if (null === $checkTypeDiplome || null === $checkTypeDomaine) {
            return $this->redirectToRoute('app_mention_index');
        }

        if ($checkTypeDiplome === 'all') {
            $typeDiplomes = $typeDiplomeRepository->findBy([], ['libelle' => 'ASC']);
        } else {
            $typeDiplomes = $checkTypeDiplome;
        }

        if ($checkTypeDomaine === 'all') {
            $domaines = $domaineRepository->findBy([], ['libelle' => 'ASC']);
        } else {
            $domaines = $checkTypeDomaine;
        }

        foreach ($typeDiplomes as $typeDiplome) {
            foreach ($domaines as $domaine) {
                $mentions = $mentionRepository->findBy(['typeDiplome' => $typeDiplome, 'domaine' => $domaine], ['libelle' => 'ASC']);
                $codeLettre = 0;
                foreach ($mentions as $mention) {
                    $mention->setCodeApogee(self::TAB_CODE_PARCOURS[$codeLettre]);
                    //si LP + plusieurs parcours sur différentes composantes alors mention lettre !=
                    foreach ($mention->getFormations() as $formation) {
                        $offset = 1;
                        foreach ($formation->getParcours() as $parcours) {
                            if (
                                count($formation->getParcours()) > 1 &&
                                $parcours->isParcoursDefaut() === false &&
                                $parcours->getComposanteInscription() !== $formation->getComposantePorteuse() &&
                                $parcours->getComposanteInscription()?->getComposanteParent() === null) {
                                //plusieurs parcours sur des composantes porteurs différentes
                                $parcours->setCodeMentionApogee(self::TAB_CODE_PARCOURS[$codeLettre]);
                                $offset++;
                            } else {
                                $parcours->setCodeMentionApogee(self::TAB_CODE_PARCOURS[$codeLettre]);
                            }
                        }
                    }
                    $codeLettre = $codeLettre + $offset;

                }
            }
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_mention_index');
    }

    #[Route('/liste', name: 'app_mention_liste', methods: ['GET'])]
    public function liste(
        Request           $request,
        MentionRepository $mentionRepository
    ): Response {
        $sort = $request->query->get('sort') ?? 'type_diplome';
        $direction = $request->query->get('direction') ?? 'asc';
        $q = $request->query->get('q') ?? '';

        $mentions = $mentionRepository->findBySearch($q, $sort, $direction);


        return $this->render('config/mention/_liste.html.twig', [
            'mentions' => $mentions,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/new', name: 'app_mention_new', methods: ['GET', 'POST'])]
    public function new(
        Request           $request,
        MentionRepository $mentionRepository
    ): Response {
        $mention = new Mention();
        $form = $this->createForm(MentionType::class, $mention, [
            'action' => $this->generateUrl('app_mention_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mentionRepository->save($mention, true);
            return $this->json(true);
        }

        return $this->render('config/mention/new.html.twig', [
            'mention' => $mention,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_mention_show', methods: ['GET'])]
    public function show(Mention $mention): Response
    {
        return $this->render('config/mention/show.html.twig', [
            'mention' => $mention,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_mention_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request           $request,
        Mention           $mention,
        MentionRepository $mentionRepository
    ): Response {
        $form = $this->createForm(MentionType::class, $mention, [
            'action' => $this->generateUrl('app_mention_edit', ['id' => $mention->getId()]),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mentionRepository->save($mention, true);

            return $this->json(true);
        }

        return $this->render('config/mention/new.html.twig', [
            'mention' => $mention,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/duplicate', name: 'app_mention_duplicate', methods: ['GET'])]
    public function duplicate(
        MentionRepository $mentionRepository,
        Mention           $mention
    ): Response {
        $mentionNew = clone $mention;
        $mentionNew->setLibelle($mention->getLibelle() . ' - Copie');
        $mentionRepository->save($mentionNew, true);
        return $this->json(true);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/{id}', name: 'app_mention_delete', methods: ['DELETE'])]
    public function delete(
        Request           $request,
        Mention           $mention,
        MentionRepository $mentionRepository
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $mention->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf')
        )) {
            $mentionRepository->remove($mention, true);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
