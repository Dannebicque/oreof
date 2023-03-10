<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\UserCentre;
use App\Form\FormationSesType;
use App\Repository\DomaineRepository;
use App\Repository\FormationRepository;
use App\Repository\MentionRepository;
use App\Repository\RoleRepository;
use App\Repository\UserCentreRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/formation')]
class FormationController extends BaseController
{
    #[Route('/', name: 'app_formation_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('formation/index.html.twig');
    }

    #[Route('/liste', name: 'app_formation_liste', methods: ['GET'])]
    public function liste(
        FormationRepository $formationRepository,
        Request $request
    ): Response
    {
        $sort = $request->query->get('sort') ?? 'typeDiplome';
        $direction = $request->query->get('direction') ?? 'asc';
        $q = $request->query->get('q') ?? null;

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_COMPOSANTE_SHOW_ALL', $this->getUser()) || $this->isGranted('ROLE_FORMATION_SHOW_ALL', $this->getUser())) {
            if ($q) {
                $formations = $formationRepository->findBySearch($q, $this->getAnneeUniversitaire(), $sort, $direction);
            } else {
                $formations = $formationRepository->findBy(['anneeUniversitaire' => $this->getAnneeUniversitaire()], [$sort => $direction]);
            }
        } else {
            $formations = [];
            $formations[] = $formationRepository->findByComposanteDpe($this->getUser(),$this->getAnneeUniversitaire(), [$sort => $direction]);
            $formations[] = $formationRepository->findBy(['responsableMention' => $this->getUser(), 'anneeUniversitaire' => $this->getAnneeUniversitaire()], [$sort => $direction]);
            $formations = array_merge(...$formations);
        }

        return $this->render('formation/_liste.html.twig', [
            'formations' => $formations,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    #[Route('/new', name: 'app_formation_new', methods: ['GET', 'POST'])]
    public function new(
        RoleRepository $roleRepository,
        MentionRepository $mentionRepository,
        UserCentreRepository $userCentreRepository,
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Request $request, FormationRepository $formationRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_FORMATION_ADD_ALL', $this->getUser());

        $formation = new Formation($this->getAnneeUniversitaire());
        $form = $this->createForm(FormationSesType::class, $formation, [
            'action' => $this->generateUrl('app_formation_new'),
            'typesDiplomes' => $typeDiplomeRegistry->getChoices(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($request->request->all()['formation_ses']['mention'] !== null) {
                $mention = $mentionRepository->find($request->request->all()['formation_ses']['mention']);
                $formation->setMentionTexte(null);
                $formation->setMention($mention);
            }

            $formation->addComposantesInscription($formation->getComposantePorteuse());
            $formationRepository->save($formation, true);

            //on v??rifie si le responsable de formation ?? le centre
            $hasCentre = $userCentreRepository->findOneBy(['user' => $formation->getResponsableMention(), 'formation' => $formation->getId()]);
            if ($hasCentre === null) {
                $role = $roleRepository->findOneBy(['code_role' => 'ROLE_RESP_FORMATION']);
                $uc = new UserCentre();
                $uc->setUser($formation->getResponsableMention());
                $uc->setFormation($formation);
                $uc->addRole($role);
                $userCentreRepository->save($uc, true);
            }
            return $this->redirectToRoute('app_formation_index');
        }

        return $this->render('formation/new.html.twig', [
            'formation' => $formation,
            'form' => $form->createView()
        ]);
    }

    #[Route('/edit/formation/{formation}', name: 'app_formation_edit_modal', methods: ['GET', 'POST'])]
    public function editModal(
        MentionRepository $mentionRepository,
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Request $request, FormationRepository $formationRepository, Formation $formation): Response
    {
        $form = $this->createForm(FormationSesType::class, $formation, [
            'action' => $this->generateUrl('app_formation_edit_modal', ['formation' => $formation->getId()]),
            'typesDiplomes' => $typeDiplomeRegistry->getChoices(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {//todo: si validate le choice de mention ne fonctionne pas
            if ($request->request->all()['formation_ses']['mention'] !== null) {
                $mention = $mentionRepository->find($request->request->all()['formation_ses']['mention']);
                $formation->setMentionTexte(null);
                $formation->setMention($mention);
            }
            $formationRepository->save($formation, true);

            return $this->redirectToRoute('app_formation_edit', ['id' => $formation->getId()]);
        }

        return $this->render('formation/editModal.html.twig', [
            'formation' => $formation,
            'form' => $form->createView()
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/api', name: 'app_formation_api', methods: ['GET'])]
    public function api(
        MentionRepository $mentionRepository,
        TypeDiplomeRegistry $typeDiplomeRegistry,
        DomaineRepository $domaineRepository,
        Request $request): Response
    {
        $domaine = $domaineRepository->find($request->query->get('domaine'));

        if ($domaine === null) {
            return $this->json([
                'mentions' => [],
            ]);
        }

        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($request->query->get('typeDiplome'));

        return $this->json([
            'mentions' => $mentionRepository->findByDomaineAndTypeDiplomeArray($domaine, $typeDiplome),
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{id}', name: 'app_formation_show', methods: ['GET'])]
    public function show(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Formation $formation): Response
    {
        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());
        return $this->render('formation/show.html.twig', [
            'template' => $typeDiplome::TEMPLATE,
            'formation' => $formation,
            'typeDiplome' => $typeDiplome
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{id}/edit', name: 'app_formation_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Formation $formation): Response
    {
        //todo: tester les droits et si on est en place "en_cours_redaction" => voter

        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());
        return $this->render('formation/edit.html.twig', [
            'formation' => $formation,
            'selectedStep' => $request->query->get('step', 1),
            'typeDiplome' => $typeDiplome
        ]);
    }

    #[Route('/{id}', name: 'app_formation_delete', methods: ['DELETE'])]
    public function delete(
        EntityManagerInterface $entityManager,
        Request $request, Formation $formation, FormationRepository $formationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $formation->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf'))) {
            foreach ($formation->getParcours() as $parcours) {
                foreach ($parcours->getBlocCompetences() as $blocs) {
                    $entityManager->remove($blocs);
                }
                $entityManager->remove($parcours);
            }
            foreach ($formation->getBlocCompetences() as $blocs) {
                $entityManager->remove($blocs);
            }
            $formationRepository->remove($formation, true);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
