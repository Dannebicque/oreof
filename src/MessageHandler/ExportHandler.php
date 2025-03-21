<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/MessageHandler/ExportHandler.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/07/2023 11:01
 */

namespace App\MessageHandler;

use App\Classes\Export\Export;
use App\Repository\CampagneCollecteRepository;
use App\Repository\ComposanteRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'async_export')]
class ExportHandler
{
    public function __construct(
        private ComposanteRepository $composanteRepository,
        private UserRepository             $userRepository,
        private CampagneCollecteRepository $campagneCollecteRepository,
        private readonly MailerInterface   $mailer,
        private Export                     $export
    )
    {
    }

    public function __invoke(\App\Message\Export $exportMessage)
    {
        $this->export->setTypeDocument($exportMessage->getTypeDocument());
        $this->export->setDate($exportMessage->getDate());

        if (null !== $exportMessage->getComposante()) {
            $composante = $this->composanteRepository->find($exportMessage->getComposante());
            $this->export->setComposante($composante);
        }

        $user = $this->userRepository->find($exportMessage->getUser());

        $lien = $this->export->exportFormations($exportMessage->getFormations(), $exportMessage->getCampagneCollecte());

        if (null !== $user && $lien !== null) {
            $mail = (new TemplatedEmail())
                ->from('oreof@univ-reims.fr')
                ->to($user->getEmail())
                ->subject('Documents prêts')
                ->htmlTemplate('mails/documents_prets.html.twig')
                ->context([
                    'user' => $user,
                    'lien' => $lien,
                ]);

            $this->mailer->send($mail);
        }
    }
}
