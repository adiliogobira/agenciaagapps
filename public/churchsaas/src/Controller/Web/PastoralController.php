<?php

namespace App\Controller\Web;

use App\Entity\PastoralAppointment;
use App\Entity\PrayerRequest;
use App\Form\PastoralAppointmentType;
use App\Form\PrayerRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/** MÓDULO 4 — Pastoral: agenda de visitas/aconselhamento e pedidos de oração. */
#[Route('/pastoral')]
#[IsGranted('MODULE_PASTORAL')]
class PastoralController extends AbstractController
{
    #[Route('', name: 'pastoral_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $appointments = $em->getRepository(PastoralAppointment::class)->findBy([], ['scheduledAt' => 'DESC'], 100);
        $prayers = $em->getRepository(PrayerRequest::class)->findBy([], ['createdAt' => 'DESC'], 100);

        return $this->render('pastoral/index.html.twig', [
            'appointments' => $appointments,
            'prayers' => $prayers,
            'types' => PastoralAppointment::TYPES,
        ]);
    }

    #[Route('/agenda/nova', name: 'pastoral_appointment_new', methods: ['GET', 'POST'])]
    public function appointmentNew(Request $request, EntityManagerInterface $em): Response
    {
        $appointment = new PastoralAppointment();
        $form = $this->createForm(PastoralAppointmentType::class, $appointment, [
            'church' => $this->getUser()->getChurch(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($appointment);
            $em->flush();
            $this->addFlash('success', 'Agendamento criado.');

            return $this->redirectToRoute('pastoral_index');
        }

        return $this->render('pastoral/appointment_form.html.twig', ['form' => $form]);
    }

    #[Route('/agenda/{id}/situacao/{status}', name: 'pastoral_appointment_status', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function appointmentStatus(Request $request, PastoralAppointment $appointment, string $status, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('status'.$appointment->getId(), $request->request->get('_token'))
            && in_array($status, ['agendado', 'realizado', 'cancelado'], true)) {
            $appointment->setStatus($status);
            $em->flush();
            $this->addFlash('success', 'Situação atualizada.');
        }

        return $this->redirectToRoute('pastoral_index');
    }

    #[Route('/oracao/nova', name: 'pastoral_prayer_new', methods: ['GET', 'POST'])]
    public function prayerNew(Request $request, EntityManagerInterface $em): Response
    {
        $prayer = new PrayerRequest();
        $form = $this->createForm(PrayerRequestType::class, $prayer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($prayer);
            $em->flush();
            $this->addFlash('success', 'Pedido de oração registrado.');

            return $this->redirectToRoute('pastoral_index');
        }

        return $this->render('pastoral/prayer_form.html.twig', ['form' => $form]);
    }

    #[Route('/oracao/{id}/respondido', name: 'pastoral_prayer_answered', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function prayerAnswered(Request $request, PrayerRequest $prayer, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('answered'.$prayer->getId(), $request->request->get('_token'))) {
            $prayer->setAnswered(!$prayer->isAnswered());
            $em->flush();
        }

        return $this->redirectToRoute('pastoral_index');
    }
}
