<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Service/ImpersonationAuditService.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 08/04/2026 15:27
 */

namespace App\Service;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use DateTimeImmutable;

/**
 * Optional audit service for logging impersonation events
 *
 * To use this, inject it in ImpersonationController and call:
 * - $this->auditService->logImpersonationStart($admin, $impersonated);
 * - $this->auditService->logImpersonationStop($admin, $impersonated);
 */
class ImpersonationAuditService
{
    public function __construct(
        private readonly LoggerInterface $logger,
    )
    {
    }

    /**
     * Log the start of an impersonation
     */
    public function logImpersonationStart(User $admin, User $impersonated): void
    {
        $message = sprintf(
            'Impersonation started: Admin %s (%s) is now impersonating %s (%s)',
            $admin->getDisplay(),
            $admin->getUsername(),
            $impersonated->getDisplay(),
            $impersonated->getUsername()
        );

        $this->logger->info($message, [
            'event' => 'impersonation_start',
            'admin_id' => $admin->getId(),
            'admin_username' => $admin->getUsername(),
            'impersonated_id' => $impersonated->getId(),
            'impersonated_username' => $impersonated->getUsername(),
            'timestamp' => (new DateTimeImmutable())->format('c'),
        ]);
    }

    /**
     * Log the stop of an impersonation
     */
    public function logImpersonationStop(User $admin, User $impersonated, ?float $duration = null): void
    {
        $message = sprintf(
            'Impersonation stopped: Admin %s (%s) stopped impersonating %s (%s)',
            $admin->getDisplay(),
            $admin->getUsername(),
            $impersonated->getDisplay(),
            $impersonated->getUsername()
        );

        if ($duration !== null) {
            $message .= sprintf(' after %.2f seconds', $duration);
        }

        $this->logger->info($message, [
            'event' => 'impersonation_stop',
            'admin_id' => $admin->getId(),
            'admin_username' => $admin->getUsername(),
            'impersonated_id' => $impersonated->getId(),
            'impersonated_username' => $impersonated->getUsername(),
            'duration_seconds' => $duration,
            'timestamp' => (new DateTimeImmutable())->format('c'),
        ]);
    }
}

