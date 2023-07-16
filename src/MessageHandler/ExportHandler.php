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
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ExportHandler
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private Export $export)
    {
    }

    public function __invoke(\App\Message\Export $exportMessage)
    {
        $this->export->setTypeDocument($exportMessage->getTypeDocument());
        $this->export->setDate($exportMessage->getDate());
        $lien = $this->export->exportFormations($exportMessage->getFormations(), $exportMessage->getAnnee());

        if (null !== $exportMessage->getUser()) {
            $mail = (new TemplatedEmail())
                ->from('oreof@univ-reims.fr')
                ->to($exportMessage->getUser()->getEmail())
                ->subject('Documents prêts')

                // path of the Twig template to render
                ->htmlTemplate('mails/documents_prets.html.twig')

                // pass variables (name => value) to the template
                ->context([
                    'user' => $exportMessage->getUser(),
                    'lien' => $lien,
                ]);

            $this->mailer->send($mail);
        }
    }
}
