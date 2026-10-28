<?php

namespace App\Controller\Web;

use App\Entity\Member;
use App\Form\MemberType;
use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

/** MÓDULO 1 — CRUD de Membros. */
#[Route('/membros')]
#[IsGranted('MODULE_MEMBERS')]
class MemberController extends AbstractController
{
    #[Route('', name: 'member_index', methods: ['GET'])]
    public function index(Request $request, MemberRepository $repo): Response
    {
        $term = $request->query->get('q');
        $status = $request->query->get('status');

        return $this->render('member/index.html.twig', [
            'members' => $repo->search($term, $status),
            'q' => $term,
            'status' => $status,
        ]);
    }

    #[Route('/novo', name: 'member_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MemberRepository $repo, SluggerInterface $slugger): Response
    {
        $member = new Member();
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePhoto($form, $member, $slugger);
            $repo->save($member);
            $this->addFlash('success', 'Membro cadastrado com sucesso.');

            return $this->redirectToRoute('member_show', ['id' => $member->getId()]);
        }

        return $this->render('member/form.html.twig', ['form' => $form, 'member' => $member]);
    }

    #[Route('/{id}', name: 'member_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Member $member): Response
    {
        return $this->render('member/show.html.twig', ['member' => $member]);
    }

    #[Route('/{id}/editar', name: 'member_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Member $member, MemberRepository $repo, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePhoto($form, $member, $slugger);
            $repo->save($member);
            $this->addFlash('success', 'Cadastro atualizado.');

            return $this->redirectToRoute('member_show', ['id' => $member->getId()]);
        }

        return $this->render('member/form.html.twig', ['form' => $form, 'member' => $member]);
    }

    #[Route('/{id}', name: 'member_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Member $member, MemberRepository $repo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$member->getId(), $request->request->get('_token'))) {
            $repo->remove($member);
            $this->addFlash('success', 'Membro removido.');
        }

        return $this->redirectToRoute('member_index');
    }

    /** Upload de foto para /public/uploads/members. */
    private function handlePhoto($form, Member $member, SluggerInterface $slugger): void
    {
        /** @var UploadedFile|null $file */
        $file = $form->has('photo') ? $form->get('photo')->getData() : null;
        if (!$file) {
            return;
        }
        $safe = $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $name = $safe.'-'.uniqid().'.'.$file->guessExtension();
        $file->move($this->getParameter('kernel.project_dir').'/public/uploads/members', $name);
        $member->setPhotoPath('uploads/members/'.$name);
    }
}
