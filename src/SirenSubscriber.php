<?php

/**
 * @file
 * Contains \Drupal\hme\SirenSubscriber.
 */

namespace Drupal\hme;

use Symfony\component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDIspatcher\EventSubscriberInterface;

/**
 * Subscribes to the kernel request event to add SIREN media types.
 */
class SirenSubscriber implements EventSubscriberInterface
{
    /**
     * Registers SIREN formats with the Request class.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     *     The event to process.
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $request->setFormat('siren_json', 'application/vnd.siren+json');
    }

    /**
     * Registers the methods in this class that should be listeners.
     *
     * @return array
     *     An array of event listener definitions.
     */
    static function getSubscribedEvents()
    {
        $events[KernelEvents::REQUEST][] = array('onKernelRequest', 40);
        return $events;
    }
}
