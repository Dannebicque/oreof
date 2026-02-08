<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Parcours/ParcoursProcessController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 05/02/2026 18:47
 */

namespace App\Controller\Parcours;

use App\Classes\JsonReponse;
use App\Classes\Process\FicheMatiereProcess;
use App\Classes\Process\ParcoursProcess;
use App\Classes\ValidationProcess;
use App\Classes\ValidationProcessFicheMatiere;
use App\Controller\BaseController;
use App\Entity\DpeParcours;
use App\Repository\FicheMatiereRepository;
use App\Repository\ParcoursRepository;
use App\Service\LheoXML;
use App\Utils\TurboStreamResponseFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route('/parcours/v2/process', name: 'parcours_process')]
#[IsGranted('ROLE_ADMIN')]
class ParcoursProcessController extends BaseController
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
    )
    {
        $this->dir = $kernel->getProjectDir() . '/public/uploads/conseils/';
    }

    #[Route('/valide/{dpeParcours}/{transition}', name: '_valider')]
    public function valide(
        DpeParcours                $dpeParcours,
        string                     $transition,
        TurboStreamResponseFactory $turboStream,
        ParcoursRepository         $parcoursRepository,
        FicheMatiereRepository     $ficheMatiereRepository,
        LheoXML                    $lheoXML,
        //string                 $etape,
        Request                    $request
    ): Response
    {
        $id = $request->query->get('id');
        $meta = $this->validationProcess->getMetaFromTransition($transition);

        // construction du formulaire

        $form = $this->createFormBuilder(null, [
            'attr' => ['id' => 'modal_form'],
            'translation_domain' => 'form'
        ]);

        if (array_key_exists('hasDate', $meta) && $meta['hasDate'] === true) {
            $form->add('date_valide', DateType::class, []);
        }

        if (array_key_exists('hasUpload', $meta) && $meta['hasUpload'] === true) {
            $form->add('file', null, [
                'required' => false,
            ])
                ->add('laisserPasser', CheckboxType::class, [
                    'required' => false,
                ]);
        }

        if (array_key_exists('hasArgumentaire', $meta) && $meta['hasArgumentaire'] === true) {
            $form->add('argumentaire', TextareaType::class, [
                'required' => true,
            ]);
        }

        $form = $form->getForm();


        $validLheo = null;
        $xmlErrorArray = [];

        $laisserPasser = false;
        //        switch ($type) {
        //            case 'parcours':
        //upload
        $fileName = '';
        if ($request->files->has('file') && $request->files->get('file') !== null) {
            $file = $request->files->get('file');
            $fileName = md5(uniqid('', true)) . '.' . $file->guessExtension();
            $file->move(
                $this->dir,
                $fileName
            );
        }

//        $process = $this->validationProcess->getEtape($etape);
        $parcours = $dpeParcours->getParcours();

        if ($parcours === null) {
            return JsonReponse::error('Parcours non trouvé');
        }


        //                if ($etape === 'cfvu') {
        //                    $laisserPasser = $getHistorique->getHistoriqueFormationLastStep($objet, 'conseil');
        //                }

        if (array_key_exists('hasValidLheo', $meta) && $meta['hasValidLheo'] === true) {
            $erreursChampsParcours = $lheoXML->checkTextValuesAreLongEnough($parcours);
            $validLheo = $lheoXML->isValidLHEO($parcours);
            if ($validLheo === false || count($erreursChampsParcours) > 0) {
                $xmlErrorArray = [];
                foreach (libxml_get_errors() as $xmlError) {
                    $xmlErrorArray[] = $lheoXML->decodeErrorMessages($xmlError->message);
                }
                $xmlErrorArray = array_merge($xmlErrorArray, $erreursChampsParcours);
                libxml_clear_errors();
            }
        }

        $processData = $this->parcoursProcess->etatParcours($dpeParcours, $meta);
        //
        //if ($request->isMethod('POST')) {
        //    return $this->parcoursProcess->valideParcours($parcours, $this->getUser(), $transition, $request, $fileName);
        //}
        //
        //break;
        //case 'ficheMatiere':
        //                $process = $this->validationProcessFicheMatiere->getEtape($etape);
        //
        //                $objet = $ficheMatiereRepository->find($id);
        //
        //                if ($objet === null) {
        //                    return JsonReponse::error('Fiche matière non trouvée');
        //                }
        //
        //                $processData = $this->ficheMatiereProcess->etatFicheMatiere($objet, $process);
        //
        //                if ($request->isMethod('POST')) {
        //                    return $this->ficheMatiereProcess->valideFicheMatiere($objet, $this->getUser(), $process, $etape, $request);
        //                }
        //                break;
        //      }

        return $turboStream->streamOpenModalFromTemplates(
            'Valider le parcours',
            'Parcours : ' . $dpeParcours->getParcours()?->getDisplay(),
            'parcours_v2/process/_valide.html.twig',
            [
                'objet' => $parcours,
                'dpeParcours' => $dpeParcours,
                'form' => $form->createView(),
                'validLheo' => $validLheo,
                'xmlErrorArray' => $xmlErrorArray,
                'processData' => $processData ?? null,
                'laisserPasser' => $laisserPasser,
                'meta' => $meta,
                'transition' => $transition,
            ],
            '_ui/_footer_submit_cancel.html.twig',
            [
                'submitLabel' => 'Valider le parcours',
            ]
        );
    }

    #[Route('/rouvrir/{dpeParcours}/{transition}', name: '_reouvrir')]
    public function reouvrir(
        TurbostreamResponseFactory $turboStream,
        DpeParcours                $dpeParcours,
        string                     $transition,
    ): Response
    {
        $meta = $this->validationProcess->getMetaFromTransition($transition);

        switch ($transition) {
            case 'reouvrir_mccc':
                $title = 'Rouvrir le parcours pour modification des MCCC/BCC/Maquette avec CFVU';
                $form = $this->createFormBuilder(null, [
                    'attr' => ['id' => 'modal_form'],
                    'translation_domain' => 'form'
                ])
                    ->add('argumentaire_demande_reouverture', TextareaType::class, [
                        //'label' => 'Motif(s) de la demande de réouverture (texte servant de justificatif en cas de passage en CFVU)  ',
                        'required' => true,
                    ])->getForm();
                break;
            case 'reouvrir_sans_cfvu':
                $title = 'Rouvrir le parcours pour modification sans CFVU';
                $form = $this->createFormBuilder(null, [
                    'attr' => ['id' => 'modal_form']
                ])
                    ->add('argumentaire_demande_reouverture_sans_cfvu', TextareaType::class, [
                        //'label' => 'Argumentaire/explication de la demande',
                        'required' => false,
                    ])->getForm();
                break;
        }


        return $turboStream->streamOpenModalFromTemplates(
            $title,
            'Parcours : ' . $dpeParcours->getParcours()?->getDisplay(),
            'parcours_v2/process/_reouvrir.html.twig',
            [
                'parcours' => $dpeParcours->getParcours(),
                'dpeParcours' => $dpeParcours,
                'meta' => $meta,
                'transition' => $transition,
                'form' => $form->createView(),
            ],
            '_ui/_footer_submit_cancel.html.twig',
            [
                'submitLabel' => 'Valider la demande',
            ]
        );
    }

    #[Route('/refuser/{dpeParcours}/{transition}', name: '_refuser')]
    public function refuser(
        TurbostreamResponseFactory $turboStream,
        DpeParcours                $dpeParcours,
        string                     $transition,
    ): Response
    {
        $meta = $this->validationProcess->getMetaFromTransition($transition);

        $form = $this->createFormBuilder(null, [
            'attr' => ['id' => 'modal_form'],
            'translation_domain' => 'form'
        ]);

        if (array_key_exists('hasDate', $meta) && $meta['hasDate'] === true) {
            $form->add('date_refus', DateType::class, []);
        }

        $form->add('argumentaire_refus', TextareaType::class, []);
        $form = $form->getForm();

        return $turboStream->streamOpenModalFromTemplates(
            'Refuser le parcours',
            'Parcours : ' . $dpeParcours->getParcours()?->getDisplay(),
            'parcours_v2/process/_refuser.html.twig',
            [
                'parcours' => $dpeParcours->getParcours(),
                'dpeParcours' => $dpeParcours,
                'meta' => $meta,
                'transition' => $transition,
                'form' => $form->createView(),
            ],
            '_ui/_footer_submit_cancel.html.twig',
            [
                'submitLabel' => 'Valider le parcours',
            ]
        );
    }

    #[Route('/reserver/{dpeParcours}/{transition}', name: '_reserver')]
    public function reserver(
        TurbostreamResponseFactory $turboStream,
        DpeParcours                $dpeParcours,
        string                     $transition,
    ): Response
    {
        $meta = $this->validationProcess->getMetaFromTransition($transition);


        $form = $this->createFormBuilder(null, [
            'attr' => ['id' => 'modal_form'],
            'translation_domain' => 'form'
        ]);

        if (array_key_exists('hasDate', $meta) && $meta['hasDate'] === true) {
            $form->add('date_reserve', DateType::class, []);
        }

        $form->add('argumentaire_reserve', TextareaType::class, []);
        $form = $form->getForm();

        return $turboStream->streamOpenModalFromTemplates(
            'Emettre des réservers sur le parcours',
            'Parcours : ' . $dpeParcours->getParcours()?->getDisplay(),
            'parcours_v2/process/_reserver.html.twig',
            [
                'parcours' => $dpeParcours->getParcours(),
                'dpeParcours' => $dpeParcours,
                'meta' => $meta,
                'form' => $form->createView(),
                'transition' => $transition,
            ],
            '_ui/_footer_submit_cancel.html.twig',
            [
                'submitLabel' => 'Valider le parcours',
            ]
        );
    }
}
