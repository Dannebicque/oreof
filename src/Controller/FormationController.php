<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationSesType;
use App\Repository\DomaineRepository;
use App\Repository\FormationRepository;
use App\Repository\MentionRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/formation')]
class FormationController extends BaseController
{
//    public function __construct(private WorkflowInterface $dpeWorkflow)
//    {}
    #[Route('/', name: 'app_formation_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('formation/index.html.twig', [
        ]);
    }

    #[Route('/liste', name: 'app_formation_liste', methods: ['GET'])]
    public function liste(FormationRepository $formationRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_COMPOSANTE_SHOW_ALL', $this->getUser()) || $this->isGranted('ROLE_FORMATION_SHOW_ALL', $this->getUser())) {
            $formations = $formationRepository->findBy(['anneeUniversitaire' => $this->getAnneeUniversitaire()]);
        } else {
            $formations = [];
            $formations[] = $formationRepository->findByComposanteDpe($this->getUser(),$this->getAnneeUniversitaire());
            $formations[] = $formationRepository->findBy(['responsableMention' => $this->getUser(), 'anneeUniversitaire' => $this->getAnneeUniversitaire()]);
            $formations = array_merge(...$formations);
        }

        return $this->render('formation/_liste.html.twig', [
            'formations' => $formations
        ]);
    }

    #[Route('/new', name: 'app_formation_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_SES')]
    public function new(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Request $request, FormationRepository $formationRepository): Response
    {
        $formation = new Formation($this->getAnneeUniversitaire());
        $form = $this->createForm(FormationSesType::class, $formation, [
            'action' => $this->generateUrl('app_formation_new'),
            'typesDiplomes' => $typeDiplomeRegistry->getChoices(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $formationRepository->save($formation, true);

            return $this->redirectToRoute('app_formation_index');
        }

        return $this->render('formation/new.html.twig', [
            'formation' => $formation,
            'form' => $form->createView()
        ]);
    }

    #[Route('/edit/formation/{formation}', name: 'app_formation_edit_modal', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_SES')]
    public function editModal(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Request $request, FormationRepository $formationRepository, Formation $formation): Response
    {
        $form = $this->createForm(FormationSesType::class, $formation, [
            'action' => $this->generateUrl('app_formation_edit_modal', ['formation' => $formation->getId()]),
            'typesDiplomes' => $typeDiplomeRegistry->getChoices(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {//todo: si validate le choice de mention ne fonctionne pas
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
    #[IsGranted('ROLE_SES')]
    public function api(
        MentionRepository $mentionRepository,
        TypeDiplomeRegistry $typeDiplomeRegistry,
        DomaineRepository $domaineRepository,
        Request $request): Response
    {
        $domaine = $domaineRepository->find($request->query->get('domaine'));
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
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Formation $formation): Response
    {
        //todo: tester les droits et si on est en place "en_cours_redaction" => voter

        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());
        return $this->render('formation/edit.html.twig', [
            'formation' => $formation,
            'step' => 1,
            'typeDiplome' => $typeDiplome
        ]);
    }

    #[Route('/{id}', name: 'app_formation_delete', methods: ['POST'])]
    public function delete(Request $request, Formation $formation, FormationRepository $formationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$formation->getId(), $request->request->get('_token'))) {
            $formationRepository->remove($formation, true);
        }

        return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
    }
}
