<?php

namespace App\Notification;

use App\Entity\User;
use App\Entity\UserWorkflowNotificationSetting;
use Doctrine\ORM\EntityManagerInterface;

final class NotificationPreferenceResolver
{
    private array $chanels = [];
    private string $source = '';

    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function resolveFor(User $user, string $workflow, ?string $transition = null): self
    {
        $pref = $user->getNotificationPreference(); // global
        $effective = [
            'email' => $pref?->isEmailEnabled() ?? true,
            'inapp' => $pref?->isInAppEnabled() ?? true,
        ];
        $source = 'global';

        $repo = $this->em->getRepository(UserWorkflowNotificationSetting::class);

        // transition-level
        if ($transition && $tr = $repo->findOneBy(['user' => $user, 'workflow' => $workflow, 'transitionName' => $transition])) {
            $effective = ['email' => $tr->isEmailEnabled(), 'inapp' => $tr->isInAppEnabled()];
            $source = 'transition';
        }

        $this->chanels = $effective;
        $this->source = $source;

        return $this;
    }

    public function channelAllowed(string $channel): bool
    {
        return $this->chanels[$channel] ?? false;
    }
}

