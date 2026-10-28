<?php

namespace App\Controller\Web;

use App\Entity\Church;
use App\Entity\User;
use App\Form\ChurchType;
use App\Form\UserAdminType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/** MÓDULO 10 — Administração: usuários, perfis de acesso e dados da igreja. */
#[Route('/admin')]
#[IsGranted('MODULE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('', name: 'admin_index', methods: ['GET'])]
    public function index(UserRepository $users): Response
    {
        $church = $this->getUser()->getChurch();

        return $this->render('admin/index.html.twig', [
            'users' => $users->findBy(['church' => $church], ['fullName' => 'ASC']),
            'church' => $church,
        ]);
    }

    /** Garante que o usuário-alvo pertence à mesma igreja do admin logado. */
    private function assertSameChurch(User $user): void
    {
        if ($user->getChurch()?->getId() !== $this->getUser()->getChurch()?->getId()) {
            throw $this->createNotFoundException();
        }
    }

    #[Route('/usuario/novo', name: 'admin_user_new', methods: ['GET', 'POST'])]
    public function userNew(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserAdminType::class, $user, ['is_new' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setChurch($this->getUser()->getChurch());
            $plain = $form->get('plainPassword')->getData();
            if ($plain) {
                $user->setPassword($hasher->hashPassword($user, $plain));
            }
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Usuário criado.');

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/user_form.html.twig', ['form' => $form, 'is_new' => true]);
    }

    #[Route('/usuario/{id}/editar', name: 'admin_user_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function userEdit(Request $request, User $user, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $this->assertSameChurch($user);
        $form = $this->createForm(UserAdminType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('plainPassword')->getData();
            if ($plain) {
                $user->setPassword($hasher->hashPassword($user, $plain));
            }
            $em->flush();
            $this->addFlash('success', 'Usuário atualizado.');

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/user_form.html.twig', ['form' => $form, 'is_new' => false]);
    }

    #[Route('/usuario/{id}/ativar', name: 'admin_user_toggle', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function userToggle(Request $request, User $user, EntityManagerInterface $em): Response
    {
        $this->assertSameChurch($user);
        if ($this->isCsrfTokenValid('toggle'.$user->getId(), $request->request->get('_token'))) {
            $user->setActive(!$user->isActive());
            $em->flush();
        }

        return $this->redirectToRoute('admin_index');
    }

    #[Route('/igreja', name: 'admin_church', methods: ['GET', 'POST'])]
    public function church(Request $request, EntityManagerInterface $em): Response
    {
        /** @var Church $church */
        $church = $this->getUser()->getChurch();
        $form = $this->createForm(ChurchType::class, $church);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Dados da igreja atualizados.');

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/church_form.html.twig', ['form' => $form]);
    }
}
