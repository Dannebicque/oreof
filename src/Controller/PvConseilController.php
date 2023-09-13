<?php

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Classes\Mailer;
use App\Classes\Process\FormationProcess;
use App\Classes\ValidationProcess;
use App\Entity\Formation;
use App\Entity\HistoriqueFormation;
use App\Events\HistoriqueFormationEvent;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PvConseilController extends AbstractController
{
    public const EMAIL_CENTRAL = 'cfvu-secretariat@univ-reims.fr'; //todo: a mettre sur établissement ?

    #[Route('/pv/conseil/{formation}', name: 'app_deposer_pv_conseil')]
    public function index(
        Mailer $myMailer,
        KernelInterface $kernel,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        Request $request,
        Formation $formation,
    ): Response
    {
        if ($request->isMethod('POST')) {
            $dir = $kernel->getProjectDir().'/public/uploads/conseils/';
            $histo = new HistoriqueFormation();
            $histo->setFormation($formation);
            $histo->setDate(new DateTime());
            $histo->setUser($this->getUser());
            $histo->setEtape('conseil');
            $histo->setEtat('valide');

            //upload
            if ($request->files->has('file') && $request->files->get('file') !== null) {
                $file = $request->files->get('file');
                $fileName = md5(uniqid('', true)) . '.' . $file->guessExtension();
                $file->move(
                    $dir,
                    $fileName
                );
                $tab['fichier'] = $fileName;
            } else {
                return JsonReponse::success($translator->trans('deposer.pv.flash.error', [], 'process'));
            }

            $histo->setComplements($tab ?? []);
            $entityManager->persist($histo);
            $entityManager->flush();

            //todo:  mail à la CFVU, avec un event ? ou workflow sur laisserPasser
            $myMailer->initEmail();
            $myMailer->setTemplate(
                'mails/workflow/formation/conseil_pv_depose.html.twig',
                ['formation' => $formation]
            );
            $myMailer->sendMessage(
                [self::EMAIL_CENTRAL],
                '[ORéOF]  Le PV de conseil a été déposé pour la formation '.$formation->getDisplayLong()
            );

            return JsonReponse::success($translator->trans('deposer.pv.flash.success', [], 'process'));
        }
        return $this->render('pv_conseil/_index.html.twig', [
            'formation' => $formation,
        ]);
    }
}
