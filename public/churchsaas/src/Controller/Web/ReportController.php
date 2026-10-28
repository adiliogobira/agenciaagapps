<?php

namespace App\Controller\Web;

use App\Entity\Event;
use App\Entity\Member;
use App\Entity\Schedule;
use App\Entity\ServiceSlot;
use App\Repository\MemberRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/** MÓDULO 9 — Relatórios: visão consolidada de membros, finanças e serviço. */
#[Route('/relatorios')]
#[IsGranted('MODULE_REPORTS')]
class ReportController extends AbstractController
{
    #[Route('', name: 'report_index', methods: ['GET'])]
    public function index(Request $request, MemberRepository $members, TransactionRepository $tx, EntityManagerInterface $em): Response
    {
        $year = (int) $request->query->get('year', date('Y'));
        $from = new \DateTimeImmutable("$year-01-01");
        $to = new \DateTimeImmutable("$year-12-31 23:59:59");

        // Membros por situação
        $byStatus = [
            'ativo' => $members->countByStatus(Member::STATUS_ATIVO),
            'inativo' => $members->countByStatus(Member::STATUS_INATIVO),
            'disciplina' => $members->countByStatus(Member::STATUS_DISCIPLINA),
            'transferido' => $members->countByStatus(Member::STATUS_TRANSFERIDO),
        ];

        // Finanças
        $income = $tx->totalByDirection('entrada', $from, $to);
        $expense = $tx->totalByDirection('saida', $from, $to);

        // Serviço (diaconia) — vagas preenchidas x abertas
        $slots = $em->getRepository(ServiceSlot::class)->findAll();
        $filled = 0; $open = 0;
        foreach ($slots as $s) { $s->isFilled() ? $filled++ : ($s->isOpen() ? $open++ : null); }

        // Escalas agrupadas por tipo (para gráfico de barras horizontais)
        $schedules = $em->getRepository(Schedule::class)->findAll();
        $byType = [];
        foreach ($schedules as $sc) {
            $label = $sc->getTypeLabel();
            $byType[$label] = ($byType[$label] ?? 0) + 1;
        }
        arsort($byType);

        return $this->render('report/index.html.twig', [
            'year' => $year,
            'byStatus' => $byStatus,
            'membersTotal' => array_sum($byStatus),
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'tithes' => $tx->totalByKind('dizimo', $from, $to),
            'offerings' => $tx->totalByKind('oferta', $from, $to),
            'cashFlow' => $tx->monthlyCashFlow($year),
            'schedulesCount' => count($schedules),
            'eventsCount' => count($em->getRepository(Event::class)->findAll()),
            'slotsFilled' => $filled,
            'slotsOpen' => $open,
            'kindsIn' => $tx->totalsByKind($from, $to, 'entrada'),
            'schedulesByType' => $byType,
        ]);
    }
}
