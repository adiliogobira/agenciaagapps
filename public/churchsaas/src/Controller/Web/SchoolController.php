<?php

namespace App\Controller\Web;

use App\Entity\ClassAttendance;
use App\Entity\SchoolClass;
use App\Entity\Student;
use App\Form\SchoolClassType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/** MÓDULO 7 — Escola Bíblica: turmas, alunos e frequência. */
#[Route('/escola')]
#[IsGranted('MODULE_SCHOOL')]
class SchoolController extends AbstractController
{
    #[Route('', name: 'school_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('school/index.html.twig', [
            'classes' => $em->getRepository(SchoolClass::class)->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/turma/nova', name: 'school_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $class = new SchoolClass();
        $form = $this->createForm(SchoolClassType::class, $class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($class);
            $em->flush();
            $this->addFlash('success', 'Turma criada. Adicione os alunos.');

            return $this->redirectToRoute('school_show', ['id' => $class->getId()]);
        }

        return $this->render('school/form.html.twig', ['form' => $form]);
    }

    #[Route('/turma/{id}', name: 'school_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(SchoolClass $class): Response
    {
        return $this->render('school/show.html.twig', ['class' => $class]);
    }

    #[Route('/turma/{id}/aluno', name: 'school_add_student', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function addStudent(Request $request, SchoolClass $class, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('student'.$class->getId(), $request->request->get('_token'))) {
            $name = trim((string) $request->request->get('fullName'));
            if ($name !== '') {
                $student = new Student();
                $student->setFullName($name);
                $student->addClass($class);
                $em->persist($student);
                $em->flush();
                $this->addFlash('success', 'Aluno adicionado.');
            }
        }

        return $this->redirectToRoute('school_show', ['id' => $class->getId()]);
    }

    #[Route('/turma/{id}/frequencia', name: 'school_attendance', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function attendance(Request $request, SchoolClass $class, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST') && $this->isCsrfTokenValid('att'.$class->getId(), $request->request->get('_token'))) {
            $date = new \DateTimeImmutable($request->request->get('date') ?: 'today');
            $present = $request->request->all('present'); // ids marcados

            foreach ($class->getStudents() as $student) {
                $att = new ClassAttendance();
                $att->setSchoolClass($class)->setStudent($student)->setDate($date)
                    ->setPresent(in_array((string) $student->getId(), $present, true));
                $em->persist($att);
            }
            $em->flush();
            $this->addFlash('success', 'Frequência registrada.');

            return $this->redirectToRoute('school_show', ['id' => $class->getId()]);
        }

        return $this->render('school/attendance.html.twig', ['class' => $class, 'today' => date('Y-m-d')]);
    }
}
