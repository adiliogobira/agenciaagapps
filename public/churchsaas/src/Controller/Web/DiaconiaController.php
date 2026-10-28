<?php

namespace App\Controller\Web;

use App\Entity\Deacon;
use App\Entity\Schedule;
use App\Entity\ScheduleAssignment;
use App\Entity\ServiceSlot;
use App\Entity\SlotWithdrawal;
use App\Form\ScheduleType;
use App\Form\ServiceSlotType;
use App\Repository\DeaconRepository;
use App\Repository\ScheduleAssignmentRepository;
use App\Repository\ScheduleRepository;
use App\Repository\ServiceSlotRepository;
use App\Service\Notifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * MÓDULO 3 — Diaconia.
 *
 * Dois modos de escala convivem:
 *  - Designação (líder escala o diácono) — fluxo original.
 *  - Escala aberta / auto-inscrição — o líder/pastor monta vagas de serviço
 *    (água, portaria, etc.), o diácono vê e ACEITA, e pode SE DESMARCAR
 *    informando o motivo. O líder acompanha quem se escalou e as saídas.
 */
#[Route('/diaconia')]
#[IsGranted('MODULE_DIACONIA')]
class DiaconiaController extends AbstractController
{
    #[Route('', name: 'diaconia_index', methods: ['GET'])]
    public function index(Request $request, ScheduleRepository $repo, DeaconRepository $deacons): Response
    {
        $type = $request->query->get('type');
        $me = $this->currentDeacon($deacons);

        return $this->render('diaconia/index.html.twig', [
            'schedules' => $repo->upcoming($type),
            'type' => $type,
            'types' => Schedule::TYPES,
            'deaconCount' => count($deacons->findBy(['active' => true])),
            'canManage' => $this->canManage($me),
            'me' => $me,
            'activities' => ServiceSlot::ACTIVITIES,
        ]);
    }

    #[Route('/escala/nova', name: 'diaconia_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ScheduleRepository $repo, DeaconRepository $deacons, Notifier $notifier): Response
    {
        $this->denyUnlessManager($deacons);

        $schedule = new Schedule();
        $form = $this->createForm(ScheduleType::class, $schedule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repo->save($schedule);

            // Notifica todos os diáconos comuns (sem liderança) sobre a nova escala.
            foreach ($deacons->findBy(['active' => true]) as $d) {
                if (!$d->isLeader()) {
                    $notifier->notifyMember(
                        $d->getMember(),
                        'Nova escala publicada',
                        'Uma nova escala foi criada: "'.$schedule->getTitle().'" em '
                            .$schedule->getScheduledAt()->format('d/m/Y H:i').'. Acesse Diaconia para se escalar.',
                        $this->generateUrl('diaconia_open'),
                        'calendar-check'
                    );
                }
            }

            $this->addFlash('success', 'Escala criada e diáconos notificados.');

            return $this->redirectToRoute('diaconia_show', ['id' => $schedule->getId()]);
        }

        return $this->render('diaconia/form.html.twig', ['form' => $form]);
    }

    #[Route('/escala/{id}', name: 'diaconia_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Schedule $schedule, DeaconRepository $deacons): Response
    {
        $assigned = array_map(fn(ScheduleAssignment $a) => $a->getDeacon()->getId(), $schedule->getAssignments()->toArray());
        $available = array_filter(
            $deacons->findBy(['active' => true]),
            fn(Deacon $d) => !in_array($d->getId(), $assigned, true)
        );

        $slotForm = $this->createForm(ServiceSlotType::class, new ServiceSlot(), [
            'action' => $this->generateUrl('diaconia_slot_new', ['id' => $schedule->getId()]),
        ]);

        return $this->render('diaconia/show.html.twig', [
            'schedule' => $schedule,
            'availableDeacons' => $available,
            'slotForm' => $slotForm,
            'canManage' => $this->canManage($this->currentDeacon($deacons)),
            'me' => $this->currentDeacon($deacons),
            'activities' => ServiceSlot::ACTIVITIES,
        ]);
    }

    // ---------------------------------------------------------------------
    // ESCALA ABERTA — auto-inscrição
    // ---------------------------------------------------------------------

    /** Vagas de serviço abertas em escalas futuras — o diácono vê e aceita. */
    #[Route('/vagas', name: 'diaconia_open', methods: ['GET'])]
    public function open(ScheduleRepository $scheduleRepo, DeaconRepository $deacons): Response
    {
        $me = $this->currentDeacon($deacons);

        // Todas as escalas futuras (mesmo as que ainda não têm vaga criada),
        // para o diácono poder se prontificar livremente.
        $schedules = $scheduleRepo->upcoming();

        return $this->render('diaconia/open.html.twig', [
            'schedules' => $schedules,
            'me' => $me,
            'activities' => ServiceSlot::ACTIVITIES,
        ]);
    }

    /** Escalas que EU (diácono logado) aceitei. */
    #[Route('/minhas-escalas', name: 'diaconia_my', methods: ['GET'])]
    public function my(ServiceSlotRepository $slots, DeaconRepository $deacons): Response
    {
        $me = $this->currentDeacon($deacons);
        $mine = $me ? $slots->upcomingForDeacon($me) : [];

        return $this->render('diaconia/my.html.twig', [
            'slots' => $mine,
            'me' => $me,
        ]);
    }

    /**
     * Auto-inscrição LIVRE: qualquer diácono pode se prontificar para servir
     * numa escala futura, escolhendo a atividade — mesmo sem uma vaga pré-criada
     * pelo líder. Cria uma vaga já preenchida por ele.
     */
    #[Route('/escala/{id}/prontificar', name: 'diaconia_volunteer', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function volunteer(Request $request, Schedule $schedule, DeaconRepository $deacons, EntityManagerInterface $em, Notifier $notifier): Response
    {
        $me = $this->currentDeacon($deacons);

        if (!$me) {
            $this->addFlash('danger', 'Seu usuário não está vinculado a um cadastro de diácono.');

            return $this->redirectToRoute('diaconia_open');
        }

        if (!$this->isCsrfTokenValid('volunteer'.$schedule->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('diaconia_open');
        }

        $activity = $request->request->get('activity', 'outro');
        if (!array_key_exists($activity, ServiceSlot::ACTIVITIES)) {
            $activity = 'outro';
        }
        $notes = trim((string) $request->request->get('notes')) ?: null;

        $slot = new ServiceSlot();
        $slot->setSchedule($schedule)
             ->setActivity($activity)
             ->setNotes($notes)
             ->setChurch($this->getUser()->getChurch());
        $slot->assignTo($me);
        $schedule->addSlot($slot);

        $em->persist($slot);
        $em->flush();

        // Avisa líderes/pastores que alguém se prontificou espontaneamente.
        $notifier->notifyChurchRoles(
            $this->getUser()->getChurch(),
            ['ROLE_PASTOR', 'ROLE_ADMIN'],
            'Diácono se prontificou',
            $me->getName().' se prontificou para "'.$slot->getActivityLabel().'" em '.$schedule->getTitle().'.',
            $this->generateUrl('diaconia_show', ['id' => $schedule->getId()]),
            'hand-thumbs-up'
        );

        $this->addFlash('success', 'Você se prontificou para servir em "'.$slot->getActivityLabel().'". Obrigado!');

        return $this->redirectToRoute('diaconia_my');
    }

    /** Líder/pastor monta uma vaga de serviço na escala. */
    #[Route('/escala/{id}/vaga', name: 'diaconia_slot_new', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function slotNew(Request $request, Schedule $schedule, DeaconRepository $deacons, EntityManagerInterface $em): Response
    {
        $this->denyUnlessManager($deacons);

        $slot = new ServiceSlot();
        $form = $this->createForm(ServiceSlotType::class, $slot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $schedule->addSlot($slot);
            $em->persist($slot);
            $em->flush();
            $this->addFlash('success', 'Vaga de serviço adicionada.');
        }

        return $this->redirectToRoute('diaconia_show', ['id' => $schedule->getId()]);
    }

    /** Líder/pastor remove uma vaga. */
    #[Route('/vaga/{id}/remover', name: 'diaconia_slot_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function slotDelete(Request $request, ServiceSlot $slot, DeaconRepository $deacons, ServiceSlotRepository $repo): Response
    {
        $this->denyUnlessManager($deacons);
        $scheduleId = $slot->getSchedule()->getId();

        if ($this->isCsrfTokenValid('slot_delete'.$slot->getId(), $request->request->get('_token'))) {
            $repo->remove($slot);
            $this->addFlash('success', 'Vaga removida.');
        }

        return $this->redirectToRoute('diaconia_show', ['id' => $scheduleId]);
    }

    /** Diácono ACEITA uma vaga aberta (auto-inscrição). */
    #[Route('/vaga/{id}/aceitar', name: 'diaconia_slot_accept', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function slotAccept(Request $request, ServiceSlot $slot, DeaconRepository $deacons, EntityManagerInterface $em, Notifier $notifier): Response
    {
        $me = $this->currentDeacon($deacons);

        if (!$me) {
            $this->addFlash('danger', 'Seu usuário não está vinculado a um cadastro de diácono.');

            return $this->redirectToRoute('diaconia_open');
        }

        if (!$this->isCsrfTokenValid('slot_accept'.$slot->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('diaconia_open');
        }

        if (!$slot->isOpen()) {
            $this->addFlash('warning', 'Esta vaga já foi preenchida por outro diácono.');
        } else {
            $slot->assignTo($me);
            $em->flush();

            $notifier->notifyChurchRoles(
                $this->getUser()->getChurch(),
                ['ROLE_PASTOR', 'ROLE_ADMIN'],
                'Vaga preenchida',
                $me->getName().' aceitou a vaga "'.$slot->getActivityLabel().'" em '.$slot->getSchedule()->getTitle().'.',
                $this->generateUrl('diaconia_show', ['id' => $slot->getSchedule()->getId()]),
                'hand-thumbs-up'
            );

            $this->addFlash('success', 'Você se escalou para: '.$slot->getActivityLabel().'.');
        }

        return $this->redirectToRoute('diaconia_my');
    }

    /** Diácono SE DESMARCA de uma vaga que aceitou, informando o motivo. */
    #[Route('/vaga/{id}/desmarcar', name: 'diaconia_slot_withdraw', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function slotWithdraw(Request $request, ServiceSlot $slot, DeaconRepository $deacons, EntityManagerInterface $em, Notifier $notifier): Response
    {
        $me = $this->currentDeacon($deacons);

        if (!$this->isCsrfTokenValid('slot_withdraw'.$slot->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('diaconia_my');
        }

        $isOwner = $me && $slot->getDeacon() && $slot->getDeacon()->getId() === $me->getId();
        if (!$isOwner && !$this->canManage($me)) {
            $this->addFlash('danger', 'Você não pode desmarcar esta vaga.');

            return $this->redirectToRoute('diaconia_my');
        }

        $reason = trim((string) $request->request->get('reason'));
        if ($reason === '') {
            $this->addFlash('warning', 'Informe o motivo da saída da escala.');

            return $this->redirectToRoute('diaconia_my');
        }

        $leaving = $slot->getDeacon();

        $log = new SlotWithdrawal();
        $log->setSlot($slot)
            ->setDeacon($leaving)
            ->setDeaconName($leaving ? $leaving->getName() : 'Diácono')
            ->setReason($reason);
        $slot->addWithdrawal($log);
        $slot->release();

        $em->persist($log);
        $em->flush();

        $notifier->notifyChurchRoles(
            $this->getUser()->getChurch(),
            ['ROLE_PASTOR', 'ROLE_ADMIN'],
            'Saída de escala',
            ($leaving ? $leaving->getName() : 'Um diácono').' saiu da vaga "'.$slot->getActivityLabel()
                .'" em '.$slot->getSchedule()->getTitle().'. Motivo: '.$reason,
            $this->generateUrl('diaconia_show', ['id' => $slot->getSchedule()->getId()]),
            'exclamation-triangle'
        );

        $this->addFlash('success', 'Você saiu da escala. O líder foi notificado com o motivo.');

        return $this->redirectToRoute('diaconia_my');
    }

    // ---------------------------------------------------------------------
    // DESIGNAÇÃO E PRESENÇA — fluxo original
    // ---------------------------------------------------------------------

    /** Designa um diácono à escala (fluxo de cima pra baixo). */
    #[Route('/escala/{id}/designar', name: 'diaconia_assign', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function assign(Request $request, Schedule $schedule, DeaconRepository $deacons, EntityManagerInterface $em, Notifier $notifier): Response
    {
        $this->denyUnlessManager($deacons);

        if (!$this->isCsrfTokenValid('assign'.$schedule->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('diaconia_show', ['id' => $schedule->getId()]);
        }

        $deacon = $deacons->find((int) $request->request->get('deacon'));
        if ($deacon) {
            $a = new ScheduleAssignment();
            $a->setSchedule($schedule)->setDeacon($deacon)
              ->setPosition($request->request->get('position'));
            $schedule->addAssignment($a);
            $em->persist($a);
            $em->flush();

            // Notifica o diácono escalado (se tiver usuário vinculado).
            $notifier->notifyMember(
                $deacon->getMember(),
                'Você foi escalado',
                'Você foi escalado para "'.$schedule->getTitle().'" em '.$schedule->getScheduledAt()->format('d/m/Y H:i').'.',
                $this->generateUrl('diaconia_show', ['id' => $schedule->getId()]),
                'calendar-check'
            );

            $this->addFlash('success', 'Diácono escalado e notificado.');
        }

        return $this->redirectToRoute('diaconia_show', ['id' => $schedule->getId()]);
    }

    /** Marca presença/ausência de um escalado (controle de presença). */
    #[Route('/presenca/{id}/{status}', name: 'diaconia_presence', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function presence(ScheduleAssignment $assignment, string $status, ScheduleAssignmentRepository $repo): Response
    {
        $valid = [
            ScheduleAssignment::PRESENCE_CONFIRMADO,
            ScheduleAssignment::PRESENCE_PRESENTE,
            ScheduleAssignment::PRESENCE_AUSENTE,
        ];
        if (in_array($status, $valid, true)) {
            $assignment->setPresence($status);
            $repo->getEntityManager()->flush();
            $this->addFlash('success', 'Presença atualizada.');
        }

        return $this->redirectToRoute('diaconia_show', ['id' => $assignment->getSchedule()->getId()]);
    }

    /** Histórico de serviços prestados por diácono. */
    #[Route('/diacono/{id}/historico', name: 'diaconia_history', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function history(Deacon $deacon): Response
    {
        return $this->render('diaconia/history.html.twig', ['deacon' => $deacon]);
    }

    // ---------------------------------------------------------------------
    // Auxiliares
    // ---------------------------------------------------------------------

    /** Resolve o cadastro de diácono do usuário logado (ou null). */
    private function currentDeacon(DeaconRepository $deacons): ?Deacon
    {
        $user = $this->getUser();
        $member = ($user !== null && method_exists($user, 'getMember')) ? $user->getMember() : null;
        if (!$member) {
            return null;
        }

        return $deacons->findOneBy(['member' => $member]);
    }

    /** Pode montar/gerir escalas: pastor, admin ou diácono marcado como líder. */
    private function canManage(?Deacon $me): bool
    {
        if ($this->isGranted('ROLE_PASTOR') || $this->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $me !== null && $me->isLeader();
    }

    private function denyUnlessManager(DeaconRepository $deacons): void
    {
        if (!$this->canManage($this->currentDeacon($deacons))) {
            throw $this->createAccessDeniedException('Apenas o líder do diaconato ou o pastor podem gerir escalas.');
        }
    }
}
