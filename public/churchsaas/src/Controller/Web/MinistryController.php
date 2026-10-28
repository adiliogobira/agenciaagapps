<?php

namespace App\Controller\Web;

use App\Entity\Ministry;
use App\Form\MinistryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/** MÓDULO Ministérios — CRUD de ministérios/departamentos. */
#[Route('/ministerios')]
#[IsGranted('MODULE_ADMIN')]
class MinistryController extends AbstractController
{
    #[Route('', name: 'ministry_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('ministry/index.html.twig', [
            'ministries' => $em->getRepository(Ministry::class)->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/novo', name: 'ministry_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $ministry = new Ministry();
        $form = $this->createForm(MinistryType::class, $ministry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($ministry);
            $em->flush();
            $this->addFlash('success', 'Ministério criado.');

            return $this->redirectToRoute('ministry_index');
        }

        return $this->render('ministry/form.html.twig', ['form' => $form, 'ministry' => null]);
    }

    #[Route('/{id}/editar', name: 'ministry_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Ministry $ministry, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MinistryType::class, $ministry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Ministério atualizado.');

            return $this->redirectToRoute('ministry_index');
        }

        return $this->render('ministry/form.html.twig', ['form' => $form, 'ministry' => $ministry]);
    }

    #[Route('/{id}/excluir', name: 'ministry_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Ministry $ministry, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ministry->getId(), $request->request->get('_token'))) {
            $em->remove($ministry);
            $em->flush();
            $this->addFlash('success', 'Ministério excluído.');
        }

        return $this->redirectToRoute('ministry_index');
    }
}
