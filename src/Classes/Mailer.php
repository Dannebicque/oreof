<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Mailer.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/02/2023 22:12
 */

namespace App\Classes;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class Mailer
{
    protected TemplatedEmail $mail;
    public const string MAIL_GENERIC = 'oreof@univ-reims.fr';

    protected Address $from;

    /**
     * MyMailer constructor.
     */
    public function __construct(
        protected MailerInterface $mailer
    ) {
        $this->from = new Address(self::MAIL_GENERIC, 'ORéOF');
    }

    public function initEmail(): void
    {
        $this->mail = new TemplatedEmail();
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendMessage(array $to, string $subject, array $options = []): void
    {
        $this->mail->from($this->from)
            ->subject($subject);

        $this->getReplyTo($options);

        $this->checkTo($to);
        $this->checkCc($options);
        $this->mailer->send($this->mail);
    }

    private function getReplyTo(array $options): void
    {
        if (array_key_exists('replyTo', $options) && '' !== $options['replyTo']) {
            if (is_array($options['replyTo'])) {
                foreach ($options['replyTo'] as $email) {
                    $this->mail->addReplyTo($this->getEmail($email));
                }
            } else {
                $this->mail->replyTo($this->getEmail($options['replyTo']));
            }
        } else {
            $this->mail->replyTo($this->getEmail($this->from));
        }
    }

    private function getEmail(string|Address|null $email): ?Address
    {
        if ($email instanceof Address) {
            return $email;
        }
        if (null !== $email && '' !== trim($email)) {
            return new Address(trim($email));
        }

        return null;
    }

    private function checkTo(array $mails): void
    {
        foreach ($mails as $m) {
            if (null !== $this->getEmail($m)) {
                $this->mail->addTo($this->getEmail($m));
            }
        }
    }

    private function checkCc(array $options): void
    {
        if (array_key_exists('cc', $options) && (is_countable($options['cc']) ? count($options['cc']) : 0) > 0) {
            foreach ($options['cc'] as $cc) {
                $this->mail->addCc($this->getEmail($cc));
            }
        }
    }

    public function attachFile(string $file): void
    {
        $this->mail->attachFromPath($file);
    }

    public function setTemplate(?string $template, ?array $data): void
    {
        if (!str_contains((string)$template, 'html')) {
            $this->mail->textTemplate($template)
                ->context($data);
        } else {
            $this->mail->htmlTemplate($template)
                ->context($data);
        }
    }
}
