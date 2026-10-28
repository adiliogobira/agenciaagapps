<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Resolve a igreja atual a partir do usuário autenticado, ativa o
 * filtro multi-tenant no Doctrine e injeta a igreja em novas entidades.
 */
class TenantSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 7],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User || $user->getChurch() === null) {
            return;
        }

        $church = $user->getChurch();
        $this->tenantContext->setChurch($church);

        $filter = $this->em->getFilters()->enable('tenant_filter');
        $filter->setParameter('tenant_id', (string) $church->getId());
    }

    /**
     * Ao criar qualquer entidade multi-tenant sem igreja definida,
     * atribui a igreja atual automaticamente.
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof TenantAwareInterface
            && $entity->getChurch() === null
            && $this->tenantContext->hasChurch()) {
            $entity->setChurch($this->tenantContext->getChurch());
        }
    }
}
