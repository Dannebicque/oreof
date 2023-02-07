<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowDpeSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            'workflow.dpe.transition.initialiser' => 'onInitialise',

        ];
    }

    public function onInitialise(Event $event)
    {
       dump($event->getSubject());
    }

}
