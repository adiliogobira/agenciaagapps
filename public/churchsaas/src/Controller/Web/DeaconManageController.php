<?php

namespace App\Controller\Web;

use App\Entity\Deacon;
use App\Entity\Member;
use App\Form\DeaconManageType;
use App\Repository\DeaconRepository;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * MÓDULO 3 — Gestão de diáconos.
 *
 * Permite ao pastor/admin:
 *   - listar todos os diáconos com destaque dos líderes;
 *   - promover/rebaixar um diácono como líder (ACL de liderança);
 *   - ativar/desativar;
 *   - registrar um membro como novo diácono.
 *
 * O "líder do diaconato" recebe ROLE_DIACONO (como qualquer diácono) mas tem
 * o campo `leader = true` na entidade Deacon. O DiaconiaController já verifica
 * este campo para permitir criar/gerir escalas.
 */
#[Route('/diaconia/gestao')]
#[IsGranted('ROLE_PASTOR')]
class DeaconManageController extends AbstractController
{
    #[Route('', name: 'deacon_manage_index', methods: ['GET'])]
    public function index(DeaconRepository $repo): Response
    {
        $deacons = $repo->findBy([], ['active' => 'DESC']);

        return $this->render('deacon/index.html.twig', [
            'deacons' => $deacons,
            'leaders' => array_filter($deacons, fn(Deacon $d) => $d->isLeader()),
        ]);
    }

    #[Route('/{id}/editar', name: 'deacon_manage_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Deacon $deacon, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DeaconManageType::class, $deacon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', sprintf(
                '%s atualizado. %s',
                $deacon->getName(),
                $deacon->isLeader() ? 'Marcado como líder do diaconato.' : ''
            ));

            return $this->redirectToRoute('deacon_manage_index');
        }

        return $this->render('deacon/edit.html.twig', ['form' => $form, 'deacon' => $deacon]);
    }

    #[Route('/novo', name: 'deacon_manage_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MemberRepository $members, EntityManagerInterface $em, DeaconRepository $repo): Response
    {
        // Membros que ainda não são diáconos
        $existing = array_map(fn(Deacon $d) => $d->getMember()->getId(), $repo->findAll());
        $available = array_filter(
            $members->findBy(['status' => 'ativo'], ['fullName' => 'ASC']),
            fn(Member $m) => !in_array($m->getId(), $existing, true)
        );

        if ($request->isMethod('POST') && $this->isCsrfTokenValid('new_deacon', $request->request->get('_token'))) {
            $member = $members->find((int) $request->request->get('member_id'));
            if ($member && !in_array($member->getId(), $existing, true)) {
                $deacon = (new Deacon())->setMember($member)->setActive(true);
                $em->persist($deacon);
                $em->flush();
                $this->addFlash('success', $member->getFullName().' registrado como diácono.');

                return $this->redirectToRoute('deacon_manage_edit', ['id' => $deacon->getId()]);
            }
        }

        return $this->render('deacon/new.html.twig', ['available' => $available]);
    }
}
