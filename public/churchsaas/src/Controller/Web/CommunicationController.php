<?php

namespace App\Controller\Web;

use App\Entity\Announcement;
use App\Form\AnnouncementType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * MÓDULO 8 — Comunicação: avisos/comunicados (mural, e-mail, WhatsApp).
 * O disparo por e-mail/WhatsApp é um ponto de integração (ver .env).
 */
#[Route('/comunicacao')]
#[IsGranted('MODULE_COMMS')]
class CommunicationController extends AbstractController
{
    #[Route('', name: 'communication_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $announcement = new Announcement();
        $form = $this->createForm(AnnouncementType::class, $announcement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mural é publicado na hora; e-mail/WhatsApp ficam prontos para o
            // serviço de integração processar (aqui apenas registramos).
            if ($announcement->getChannel() === 'mural') {
                $announcement->setSentAt(new \DateTimeImmutable());
                $this->addFlash('success', 'Aviso publicado no mural.');
            } else {
                $this->addFlash('info', 'Comunicado registrado. O envio por '
                    .$announcement->getChannelLabel().' será processado pela integração configurada.');
            }
            $em->persist($announcement);
            $em->flush();

            return $this->redirectToRoute('communication_index');
        }

        return $this->render('communication/index.html.twig', [
            'form' => $form,
            'announcements' => $em->getRepository(Announcement::class)->findBy([], ['createdAt' => 'DESC'], 50),
        ]);
    }
}
