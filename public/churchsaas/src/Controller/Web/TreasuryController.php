<?php

namespace App\Controller\Web;

use App\Entity\Transaction;
use App\Form\TransactionType;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/** MÓDULO 5 — Tesouraria: lançamentos, dízimos, ofertas, campanhas e fluxo de caixa. */
#[Route('/tesouraria')]
#[IsGranted('MODULE_TREASURY')]
class TreasuryController extends AbstractController
{
    #[Route('', name: 'treasury_index', methods: ['GET'])]
    public function index(Request $request, TransactionRepository $repo): Response
    {
        $year = (int) $request->query->get('year', date('Y'));
        $from = new \DateTimeImmutable("$year-01-01");
        $to = new \DateTimeImmutable("$year-12-31 23:59:59");

        $income = $repo->totalByDirection('entrada', $from, $to);
        $expense = $repo->totalByDirection('saida', $from, $to);

        return $this->render('treasury/index.html.twig', [
            'transactions' => $repo->findBy([], ['occurredAt' => 'DESC'], 100),
            'year' => $year,
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'tithes' => $repo->totalByKind('dizimo', $from, $to),
            'offerings' => $repo->totalByKind('oferta', $from, $to),
            'cashFlow' => $repo->monthlyCashFlow($year),
            'kindsIn' => $repo->totalsByKind($from, $to, 'entrada'),
            'kindsOut' => $repo->totalsByKind($from, $to, 'saida'),
        ]);
    }

    #[Route('/lancamento/novo', name: 'treasury_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TransactionRepository $repo): Response
    {
        $tx = new Transaction();
        $form = $this->createForm(TransactionType::class, $tx);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repo->save($tx);
            $this->addFlash('success', 'Lançamento registrado.');

            return $this->redirectToRoute('treasury_index');
        }

        return $this->render('treasury/form.html.twig', ['form' => $form]);
    }

    #[Route('/lancamento/{id}', name: 'treasury_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Transaction $tx, TransactionRepository $repo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tx->getId(), $request->request->get('_token'))) {
            $repo->remove($tx);
            $this->addFlash('success', 'Lançamento excluído.');
        }

        return $this->redirectToRoute('treasury_index');
    }
}
