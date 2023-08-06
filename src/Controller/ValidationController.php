<?php

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Classes\ValidationProcess;
use App\Entity\Historique;
use App\Entity\HistoriqueFormation;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\Yaml\Yaml;

class ValidationController extends AbstractController
{
    public function __construct(private ValidationProcess $validationProcess)
    {
    }

    #[Route('/validation/valide/{etape}', name: 'app_validation_valide')]
    public function valide(
        EntityManagerInterface $entityManager,
        #[Target('dpe')]
        WorkflowInterface $dpeWorkflow,
        FormationRepository $formationRepository,
        string $etape, Request $request): Response
    {
        $type = $request->query->get('type');
        $id = $request->query->get('id');
        $definition = $dpeWorkflow->getDefinition();

        $process = $this->validationProcess->getEtape($etape);

        switch ($type)
        {
            case 'formation':
                $objet = $formationRepository->find($id);
                $place = $dpeWorkflow->getMarking($objet);
                $transitions = $dpeWorkflow->getEnabledTransitions($objet);
                break;
            case 'parcours':
                $objet = $formationRepository->find($id);
                $place = $dpeWorkflow->getMarking($objet);
                $transitions = $dpeWorkflow->getEnabledTransitions($objet);
                if ($request->isMethod('POST')) {
                    $dpeWorkflow->apply($objet, $process['canValide']); //todo: a rendre dynamique, next step ou step de validation, de refus oud e reserve
                    //todo: ajouter dans l'historique, récupérer les élements du formulaire...
                    $histo = new HistoriqueFormation();
                    $histo->setFormation($objet);
                    $histo->setCreated(new \DateTime());
                    $histo->setUser($this->getUser());
                    $histo->setEtape($etape);
                    $histo->setEtat('valide');
                    $entityManager->persist($histo);
                    $entityManager->flush();
                    return JsonReponse::success('ok');
                }
                break;
            case 'ficheMatiere':
                $objet = $formationRepository->find($id);
                $place = $dpeWorkflow->getMarking($objet);
                $transitions = $dpeWorkflow->getEnabledTransitions($objet);
                break;
        }

        return $this->render('validation/_valide.html.twig', [
            'process' => $process,
            'place' => array_keys($place->getPlaces())[0],
            'transitions' => $transitions,
            'defintion' => $definition,
            'type' => $type,
            'id' => $id,
            'etape' => $etape,
        ]);
    }

    #[Route('/validation/refuse/{etape}', name: 'app_validation_refuse')]
    public function refuse(
        EntityManagerInterface $entityManager,
        #[Target('dpe')]
        WorkflowInterface $dpeWorkflow,
        FormationRepository $formationRepository,
        string $etape, Request $request): Response
    {
        $type = $request->query->get('type');
        $id = $request->query->get('id');
        $definition = $dpeWorkflow->getDefinition();

        $process = $this->validationProcess->getEtape($etape);

        switch ($type)
        {
            case 'formation':
                $objet = $formationRepository->find($id);
                $place = $dpeWorkflow->getMarking($objet);
                $transitions = $dpeWorkflow->getEnabledTransitions($objet);
                break;
            case 'parcours':
                $objet = $formationRepository->find($id);
                $place = $dpeWorkflow->getMarking($objet);
                $transitions = $dpeWorkflow->getEnabledTransitions($objet);
                if ($request->isMethod('POST')) {
                    $dpeWorkflow->apply($objet, $process['canRefuse']); //todo: a rendre dynamique, next step ou step de validation, de refus oud e reserve
                    //todo: ajouter dans l'historique, récupérer les élements du formulaire...
                    $histo = new HistoriqueFormation();
                    $histo->setFormation($objet);
                    $histo->setCreated(new \DateTime());
                    $histo->setUser($this->getUser());
                    $histo->setEtape($etape);
                    $histo->setCommentaire($request->request->get('commentaire'));
                    $histo->setEtat('refuse');
                    $entityManager->persist($histo);
                    $entityManager->flush();
                    return JsonReponse::success('ok');
                }
                break;
            case 'ficheMatiere':
                $objet = $formationRepository->find($id);
                $place = $dpeWorkflow->getMarking($objet);
                $transitions = $dpeWorkflow->getEnabledTransitions($objet);
                break;
        }

        return $this->render('validation/_refuse.html.twig', [
            'process' => $process,
            'place' => array_keys($place->getPlaces())[0],
            'transitions' => $transitions,
            'defintion' => $definition,
            'type' => $type,
            'id' => $id,
            'etape' => $etape,
        ]);
    }

    #[Route('/validation/reserve/{etape}', name: 'app_validation_reserve')]
    public function reserve(string $etape, Request $request): Response
    {
        return $this->render('validation/_reserve.html.twig', [
            'process' => $this->validationProcess->getEtape($etape),
        ]);
    }
}
