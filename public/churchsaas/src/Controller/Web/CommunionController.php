<?php

namespace App\Controller\Web;

use App\Entity\CommunionSeat;
use App\Entity\CommunionTable;
use App\Entity\Member;
use App\Entity\Schedule;
use App\Repository\MemberRepository;
use App\Service\Notifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * MÓDULO 3 — Mesa da Santa Ceia.
 *
 * Exclusivo para pastores: elabora quem compõe a mesa, quem ora pelos
 * elementos e quem os consagra. Os convocados (se membros cadastrados)
 * recebem notificação automática.
 */
#[Route('/diaconia/escala/{scheduleId}/ceia')]
#[IsGranted('ROLE_PASTOR')]
class CommunionController extends AbstractController
{
    #[Route('', name: 'communion_show', methods: ['GET'])]
    public function show(int $scheduleId, EntityManagerInterface $em, MemberRepository $members): Response
    {
        $schedule = $this->getSchedule($scheduleId, $em);
        $table    = $this->getOrCreateTable($schedule, $em);

        return $this->render('communion/show.html.twig', [
            'schedule' => $schedule,
            'table'    => $table,
            'members'  => $members->findBy(['status' => 'ativo'], ['fullName' => 'ASC']),
            'roles'    => CommunionSeat::ROLES,
        ]);
    }

    #[Route('/assento', name: 'communion_seat_add', methods: ['POST'])]
    public function addSeat(Request $request, int $scheduleId, EntityManagerInterface $em, MemberRepository $members, Notifier $notifier): Response
    {
        if (!$this->isCsrfTokenValid('seat'.$scheduleId, $request->request->get('_token'))) {
            return $this->redirectToRoute('communion_show', ['scheduleId' => $scheduleId]);
        }

        $schedule = $this->getSchedule($scheduleId, $em);
        $table    = $this->getOrCreateTable($schedule, $em);

        $personName = trim((string) $request->request->get('personName'));
        $role       = $request->request->get('role', 'composicao');
        $memberId   = (int) $request->request->get('member_id');

        if ($personName === '') {
            $this->addFlash('warning', 'Informe o nome da pessoa.');
            return $this->redirectToRoute('communion_show', ['scheduleId' => $scheduleId]);
        }

        if (!array_key_exists($role, CommunionSeat::ROLES)) {
            $role = 'composicao';
        }

        $member = $memberId ? $members->find($memberId) : null;

        $seat = new CommunionSeat();
        $seat->setTable($table)
             ->setPersonName($personName)
             ->setRole($role)
             ->setMember($member)
             ->setNotes($request->request->get('notes') ?: null)
             ->setChurch($this->getUser()->getChurch());

        $table->addSeat($seat);
        $em->persist($seat);
        $em->flush();

        // Notifica o membro cadastrado (pastor/missionário) que foi convocado.
        if ($member !== null) {
            $notifier->notifyMember(
                $member,
                'Convocação para a Mesa da Ceia',
                sprintf(
                    'Você foi convocado para "%s" na Mesa da Santa Ceia em %s (%s).',
                    CommunionSeat::ROLES[$role],
                    $schedule->getTitle(),
                    $schedule->getScheduledAt()->format('d/m/Y H:i')
                ),
                $this->generateUrl('communion_show', ['scheduleId' => $scheduleId]),
                'cup-hot'
            );
        }

        $this->addFlash('success', $personName.' adicionado(a) à mesa.');

        return $this->redirectToRoute('communion_show', ['scheduleId' => $scheduleId]);
    }

    #[Route('/assento/{id}/remover', name: 'communion_seat_remove', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function removeSeat(Request $request, int $scheduleId, CommunionSeat $seat, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('seat_rm'.$seat->getId(), $request->request->get('_token'))) {
            $em->remove($seat);
            $em->flush();
            $this->addFlash('success', 'Removido da mesa.');
        }

        return $this->redirectToRoute('communion_show', ['scheduleId' => $scheduleId]);
    }

    #[Route('/notas', name: 'communion_notes', methods: ['POST'])]
    public function updateNotes(Request $request, int $scheduleId, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('notes'.$scheduleId, $request->request->get('_token'))) {
            $table = $this->getOrCreateTable($this->getSchedule($scheduleId, $em), $em);
            $table->setNotes($request->request->get('notes') ?: null);
            $em->flush();
        }

        return $this->redirectToRoute('communion_show', ['scheduleId' => $scheduleId]);
    }

    // ---- helpers ----

    private function getSchedule(int $id, EntityManagerInterface $em): Schedule
    {
        $s = $em->find(Schedule::class, $id);
        if (!$s) {
            throw $this->createNotFoundException('Escala não encontrada.');
        }
        return $s;
    }

    private function getOrCreateTable(Schedule $schedule, EntityManagerInterface $em): CommunionTable
    {
        $repo  = $em->getRepository(CommunionTable::class);
        $table = $repo->findOneBy(['schedule' => $schedule]);

        if (!$table) {
            $table = (new CommunionTable())
                ->setSchedule($schedule)
                ->setChurch($this->getUser()->getChurch());
            $em->persist($table);
            $em->flush();
        }

        return $table;
    }
}
