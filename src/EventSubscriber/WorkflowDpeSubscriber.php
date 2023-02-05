<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowDpeSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            'workflow.dpe.enter' => 'onEnter',
            'workflow.dpe.guard' => 'onGuard',
        ];
    }

    public function onEnter(Event $event)
    {
      //  dump($event);
    }

    public function onGuard(Event $event)
    {
        //dump($event);
    }
}
