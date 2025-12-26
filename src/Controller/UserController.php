<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Ulid;

#[Route('/users', name: 'app_user_')]
final class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private UserRepository $userRepo
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $users = $this->userRepo->findAll();
        return $this->json($users, Response::HTTP_OK, [], ['groups' => ['user:read']]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $user = $this->userRepo->find(new Ulid($id));
        
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json($user, Response::HTTP_OK, [], ['groups' => ['user:read']]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            ['groups' => ['user:write']]
        );
        
        $this->em->persist($user);
        $this->em->flush();
        
        return $this->json($user, Response::HTTP_CREATED, [], ['groups' => ['user:read']]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $user = $this->userRepo->find(new Ulid($id));
        
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            ['object_to_populate' => $user, 'groups' => ['user:write']]
        );
        
        $this->em->flush();
        
        return $this->json($user, Response::HTTP_OK, [], ['groups' => ['user:read']]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $user = $this->userRepo->find(new Ulid($id));
        
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        $this->em->remove($user);
        $this->em->flush();
        
        return $this->json(['message' => 'User deleted successfully'], Response::HTTP_OK);
    }
}
