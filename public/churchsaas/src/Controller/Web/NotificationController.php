<?php

namespace App\Controller\Web;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notificacoes')]
class NotificationController extends AbstractController
{
    #[Route('', name: 'notification_index', methods: ['GET'])]
    public function index(NotificationRepository $repo): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('notification/index.html.twig', [
            'notifications' => $repo->allFor($user),
        ]);
    }

    #[Route('/ler-todas', name: 'notification_read_all', methods: ['POST'])]
    public function readAll(Request $request, NotificationRepository $repo): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('read_all', $request->request->get('_token'))) {
            $repo->markAllRead($user);
        }

        return $this->redirectToRoute('notification_index');
    }

    #[Route('/{id}/abrir', name: 'notification_open', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function open(Notification $notification, EntityManagerInterface $em): Response
    {
        // Só o dono pode abrir a própria notificação.
        if ($notification->getUser()?->getId() !== $this->getUser()->getId()) {
            throw $this->createNotFoundException();
        }
        $notification->setRead(true);
        $em->flush();

        return $this->redirect($notification->getLink() ?: $this->generateUrl('notification_index'));
    }
}
