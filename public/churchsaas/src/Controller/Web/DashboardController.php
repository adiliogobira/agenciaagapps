<?php

namespace App\Controller\Web;

use App\Entity\Member;
use App\Repository\MemberRepository;
use App\Repository\ScheduleRepository;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/** MÓDULO 10 — Painel administrativo / dashboard executivo com indicadores. */
class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(
        MemberRepository $members,
        ScheduleRepository $schedules,
        TransactionRepository $transactions,
    ): Response {
        $now = new \DateTimeImmutable();
        $monthStart = $now->modify('first day of this month')->setTime(0, 0);
        $monthEnd = $now->modify('last day of this month')->setTime(23, 59);

        $income = $transactions->totalByDirection('entrada', $monthStart, $monthEnd);
        $expense = $transactions->totalByDirection('saida', $monthStart, $monthEnd);

        return $this->render('dashboard/index.html.twig', [
            'membersActive' => $members->countByStatus(Member::STATUS_ATIVO),
            'membersInactive' => $members->countByStatus(Member::STATUS_INATIVO),
            'birthdays' => $members->birthdaysInMonth((int) $now->format('n')),
            'upcomingSchedules' => $schedules->upcoming(null, 8),
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'tithes' => $transactions->totalByKind('dizimo', $monthStart, $monthEnd),
            'offerings' => $transactions->totalByKind('oferta', $monthStart, $monthEnd),
            'cashFlow' => $transactions->monthlyCashFlow((int) $now->format('Y')),
            'kindsIn' => $transactions->totalsByKind($monthStart, $monthEnd, 'entrada'),
        ]);
    }
}
