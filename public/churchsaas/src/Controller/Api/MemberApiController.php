<?php

namespace App\Controller\Api;

use App\Entity\Member;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * API REST — Membros. O filtro multi-tenant garante que cada token/igreja
 * só acesse seus próprios registros.
 *
 * Autenticação: firewall "api" (http_basic na base; troque por JWT em produção).
 */
#[Route('/api/membros', name: 'api_member_')]
class MemberApiController extends AbstractController
{
    public function __construct(
        private readonly MemberRepository $repo,
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $members = $this->repo->search($request->query->get('q'), $request->query->get('status'));

        return $this->json($members, Response::HTTP_OK, [], ['groups' => 'member:read'] + $this->ctx());
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Member $member): JsonResponse
    {
        return $this->json($member, Response::HTTP_OK, [], $this->ctx());
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        /** @var Member $member */
        $member = $this->serializer->deserialize($request->getContent(), Member::class, 'json');

        $errors = $this->validator->validate($member);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // O TenantSubscriber (prePersist) injeta a igreja automaticamente.
        $this->em->persist($member);
        $this->em->flush();

        return $this->json($member, Response::HTTP_CREATED, [], $this->ctx());
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(Request $request, Member $member): JsonResponse
    {
        $this->serializer->deserialize(
            $request->getContent(),
            Member::class,
            'json',
            ['object_to_populate' => $member]
        );
        $this->em->flush();

        return $this->json($member, Response::HTTP_OK, [], $this->ctx());
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(Member $member): JsonResponse
    {
        $this->em->remove($member);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /** Evita recursão infinita na serialização de relações. */
    private function ctx(): array
    {
        return [
            'circular_reference_handler' => fn($o) => method_exists($o, 'getId') ? $o->getId() : null,
            'ignored_attributes' => ['church', 'users', 'assignments'],
        ];
    }
}
