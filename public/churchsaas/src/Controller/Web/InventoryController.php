<?php

namespace App\Controller\Web;

use App\Entity\InventoryItem;
use App\Entity\InventoryMovement;
use App\Form\InventoryItemType;
use App\Service\Notifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * MÓDULO Estoque de Doações — mantimentos e materiais de limpeza.
 * Controle por nome e quantidade, SEM valores monetários.
 */
#[Route('/estoque')]
#[IsGranted('MODULE_INVENTORY')]
class InventoryController extends AbstractController
{
    #[Route('', name: 'inventory_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $items = $em->getRepository(InventoryItem::class)->findBy([], ['category' => 'ASC', 'name' => 'ASC']);

        // Agrupa por categoria para exibição.
        $grouped = [];
        foreach ($items as $i) {
            $grouped[$i->getCategory()][] = $i;
        }

        // Dados para os gráficos: quantidade total por categoria e itens em destaque
        $byCategory = [];
        foreach ($items as $i) {
            $label = $i->getCategoryLabel();
            $byCategory[$label] = ($byCategory[$label] ?? 0) + $i->getQuantity();
        }

        $top = $items;
        usort($top, fn(InventoryItem $a, InventoryItem $b) => $b->getQuantity() <=> $a->getQuantity());
        $top = array_slice($top, 0, 8);
        $topItems = [];
        foreach ($top as $i) {
            $topItems[$i->getName()] = $i->getQuantity();
        }

        return $this->render('inventory/index.html.twig', [
            'grouped' => $grouped,
            'categories' => InventoryItem::CATEGORIES,
            'lowCount' => count(array_filter($items, fn(InventoryItem $i) => $i->isLow())),
            'byCategory' => $byCategory,
            'topItems' => $topItems,
        ]);
    }

    #[Route('/item/novo', name: 'inventory_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $item = new InventoryItem();
        $form = $this->createForm(InventoryItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($item);
            $em->flush();
            $this->addFlash('success', 'Item cadastrado.');

            return $this->redirectToRoute('inventory_show', ['id' => $item->getId()]);
        }

        return $this->render('inventory/item_form.html.twig', ['form' => $form]);
    }

    #[Route('/item/{id}', name: 'inventory_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(InventoryItem $item): Response
    {
        return $this->render('inventory/show.html.twig', ['item' => $item]);
    }

    /** Registra entrada (doação recebida) ou saída (consumo/distribuição). */
    #[Route('/item/{id}/movimentar', name: 'inventory_move', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function move(Request $request, InventoryItem $item, EntityManagerInterface $em, Notifier $notifier): Response
    {
        if (!$this->isCsrfTokenValid('move'.$item->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('inventory_show', ['id' => $item->getId()]);
        }

        $direction = $request->request->get('direction') === InventoryMovement::OUT
            ? InventoryMovement::OUT : InventoryMovement::IN;
        $qty = (float) str_replace(',', '.', (string) $request->request->get('quantity'));

        if ($qty <= 0) {
            $this->addFlash('warning', 'Informe uma quantidade válida.');

            return $this->redirectToRoute('inventory_show', ['id' => $item->getId()]);
        }

        $mov = new InventoryMovement();
        $mov->setItem($item)->setDirection($direction)->setQuantity($qty)
            ->setDonor($request->request->get('donor') ?: null)
            ->setNotes($request->request->get('notes') ?: null)
            ->setRegisteredBy($this->getUser());
        $item->getMovements()->add($mov);

        // Atualiza o saldo do item.
        $item->addQuantity($direction === InventoryMovement::IN ? $qty : -$qty);

        $em->persist($mov);
        $em->flush();

        // Alerta de estoque baixo para líderes/pastores.
        if ($item->isLow()) {
            $notifier->notifyChurchRoles(
                $this->getUser()->getChurch(),
                ['ROLE_PASTOR', 'ROLE_ADMIN'],
                'Estoque baixo',
                'O item "'.$item->getName().'" está com estoque baixo ('
                    .rtrim(rtrim(number_format($item->getQuantity(), 2, ',', '.'), '0'), ',').' '.$item->getUnit().').',
                $this->generateUrl('inventory_show', ['id' => $item->getId()]),
                'box-seam'
            );
        }

        $this->addFlash('success', 'Movimentação registrada.');

        return $this->redirectToRoute('inventory_show', ['id' => $item->getId()]);
    }

    #[Route('/item/{id}/excluir', name: 'inventory_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, InventoryItem $item, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$item->getId(), $request->request->get('_token'))) {
            $em->remove($item);
            $em->flush();
            $this->addFlash('success', 'Item removido.');
        }

        return $this->redirectToRoute('inventory_index');
    }
}
