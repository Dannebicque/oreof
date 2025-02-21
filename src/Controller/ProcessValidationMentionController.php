<?php

namespace App\Controller;

use App\Classes\MentionProcess;
use App\Classes\Process\FicheMatiereProcess;
use App\Classes\Process\ParcoursProcess;
use App\Classes\ValidationProcess;
use App\Classes\ValidationProcessFicheMatiere;
use App\Classes\verif\FormationValide;
use App\Entity\Formation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProcessValidationMentionController extends BaseController
{
    private string $dir;

    public function __construct(
        private readonly EventDispatcherInterface      $eventDispatcher,
        private readonly EntityManagerInterface        $entityManager,
        private readonly ValidationProcess             $validationProcess,
        private readonly ValidationProcessFicheMatiere $validationProcessFicheMatiere,
        private readonly ParcoursProcess               $parcoursProcess,
        private readonly FicheMatiereProcess           $ficheMatiereProcess,
        KernelInterface                                $kernel
    ) {
        $this->dir = $kernel->getProjectDir() . '/public/uploads/conseils/';
    }

    #[Route('/validation-mention/valide/{etape}/{formation}', name: 'app_validation_formation_valide')]
    public function valide(
        MentionProcess         $mentionProcess,
        Formation $formation,
        string                 $etape,
        Request                $request
    ): Response {
        $valideFormation = new FormationValide($formation);
//        $process = $this->validationProcess->getEtape($etape);
//        $processData = $this->parcoursProcess->etatParcours($parcours, $process);//todo: process??
        if ($request->isMethod('POST')) {
            return $this->parcoursProcess->valideParcours($parcours, $this->getUser(), $transition, $request, $fileName);
        }


        return $this->render('process_validation_mention/_valide.html.twig', [
            'formation' => $formation,
            'valide' => $valideFormation->valideFormation(),
            'isValid' => $valideFormation->isFormationValide(),
            'etape' => $etape,
        ]);
    }
}
