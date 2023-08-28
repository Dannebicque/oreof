<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/EventSubscriber/WorkflowDpeMailSubscriber.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\EventSubscriber;

use App\Classes\Mailer;
use App\Entity\Formation;
use App\Entity\HistoriqueFormation;
use App\Entity\HistoriqueParcours;
use App\Events\HistoriqueFormationEvent;
use App\Events\HistoriqueParcoursEvent;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;
use App\Repository\UserRepository;
use App\Utils\Tools;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Workflow\Event\Event;

class HistoriqueSubscriber implements EventSubscriberInterface
{
    protected array $cases = [
        'laisserPasser',
        'sousReserveModifications',
        'sousReserveConseil',
        'projetARevoir'
    ];

    private string $dir;

    public function __construct(
        KernelInterface              $kernel,
        protected UserRepository         $userRepository,
        protected ComposanteRepository   $composanteRepository,
        protected FormationRepository    $formationRepository,
        protected EntityManagerInterface $entityManager
    ) {
        $this->dir = $kernel->getProjectDir().'/public/uploads/conseils/';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            HistoriqueFormationEvent::ADD_HISTORIQUE_FORMATION => 'createHistoriqueFormation',
            HistoriqueParcoursEvent::ADD_HISTORIQUE_PARCOURS => 'createHistoriqueParcours',
//            'add.historique.parcours' => 'createHistoriqueParcours',
//            'add.historique.ficheEc' => 'createHistoriqueFicheEc',

        ];
    }


    public function createHistoriqueFormation(HistoriqueFormationEvent $event): void
    {
        $request = $event->getRequest();

        if ($request === null) {
            throw new \Exception('Pas de requete');
        }

        $histo = new HistoriqueFormation();
        $histo->setFormation($event->getFormation());
        $histo->setDate($this->getDateTime($request));
        $histo->setUser($event->getUser());
        $histo->setEtape($event->getEtape());
        $histo->setCommentaire($this->getCommentaire($request));
        $histo->setEtat($event->getEtat());

        foreach ($this->cases as $cas) {
            if ($request->request->has($cas)) {
                $tab[$cas] = $request->request->get($cas);
            }
        }

        //upload
        if ($request->files->has('file')) {
            $file = $request->files->get('file');
            $fileName = md5(uniqid('', true)) . '.' . $file->guessExtension();
            $file->move(
                $this->dir,
                $fileName
            );
            $tab['fichier'] = $fileName;
        }

        $histo->setComplements($tab ?? []);

        $this->entityManager->persist($histo);
        $this->entityManager->flush();
    }

    public function createHistoriqueParcours(HistoriqueParcoursEvent $event): void
    {
        $request = $event->getRequest();

        if ($request === null) {
            throw new \Exception('Pas de requete');
        }

        $histo = new HistoriqueParcours();
        $histo->setParcours($event->getParcours());
        $histo->setDate($this->getDateTime($request));
        $histo->setUser($event->getUser());
        $histo->setEtape($event->getEtape());
        $histo->setCommentaire($this->getCommentaire($request));
        $histo->setEtat($event->getEtat());

        $this->entityManager->persist($histo);
        $this->entityManager->flush();
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request|null $request
     * @return \DateTime|\DateTimeInterface
     */
    private function getDateTime(?\Symfony\Component\HttpFoundation\Request $request): \DateTimeInterface|\DateTime
    {
        if ($request->request->has('date')) {
            $date = Tools::convertDate($request->request->get('date'));
        } else {
            $date = new \DateTime();
        }
        return $date;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool|float|int|string|null
     */
    private function getCommentaire(\Symfony\Component\HttpFoundation\Request $request): string|int|bool|null|float
    {
        if ($request->request->has('commentaire')) {
            $commentaire = $request->request->get('commentaire');
        } else {
            $commentaire = '';
        }
        return $commentaire;
    }
}
