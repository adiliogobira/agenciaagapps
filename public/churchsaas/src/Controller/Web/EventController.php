<?php

namespace App\Controller\Web;

use App\Entity\Event;
use App\Entity\EventRegistration;
use App\Form\EventType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/** MÓDULO 6 — Eventos: congressos, vigílias, retiros e inscrições. */
#[Route('/eventos')]
#[IsGranted('MODULE_EVENTS')]
class EventController extends AbstractController
{
    #[Route('', name: 'event_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $em->getRepository(Event::class)->findBy([], ['startsAt' => 'DESC']),
            'types' => Event::TYPES,
        ]);
    }

    #[Route('/novo', name: 'event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($event);
            $em->flush();
            $this->addFlash('success', 'Evento criado.');

            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        return $this->render('event/form.html.twig', ['form' => $form]);
    }

    #[Route('/{id}', name: 'event_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', ['event' => $event]);
    }

    #[Route('/{id}/inscrever', name: 'event_register', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function register(Request $request, Event $event, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('register'.$event->getId(), $request->request->get('_token'))) {
            $name = trim((string) $request->request->get('participantName'));
            if ($name !== '') {
                $reg = new EventRegistration();
                $reg->setEvent($event)->setParticipantName($name);
                $em->persist($reg);
                $em->flush();
                $this->addFlash('success', 'Inscrição registrada.');
            }
        }

        return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
    }

    #[Route('/inscricao/{id}/pagamento', name: 'event_registration_payment', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function togglePayment(Request $request, EventRegistration $reg, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('pay'.$reg->getId(), $request->request->get('_token'))) {
            $reg->setPaymentStatus($reg->getPaymentStatus() === 'pago' ? 'pendente' : 'pago');
            $em->flush();
        }

        return $this->redirectToRoute('event_show', ['id' => $reg->getEvent()->getId()]);
    }

    #[Route('/{id}/excluir', name: 'event_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $em->remove($event);
            $em->flush();
            $this->addFlash('success', 'Evento excluído.');
        }

        return $this->redirectToRoute('event_index');
    }
}
