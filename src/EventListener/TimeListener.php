<?php

namespace App\EventListener;

use DateTime;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Summary of TimeListener
 */
class TimeListener implements EventSubscriber
{
    /**
     * Summary of getSubscribedEvents
     * @return array<string>
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    /**
     * Summary of prePersist
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     * @return void
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (method_exists($entity, 'setCreatedAt')) {
            $entity->setCreatedAt(new DateTime('now'));
        }
    }

    /**
     * Summary of preUpdate
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     * @return void
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt(new DateTime('now'));
        }
    }
}
