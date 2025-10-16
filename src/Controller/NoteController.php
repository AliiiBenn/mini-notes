<?php

namespace App\Controller;

use App\Entity\Note;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/notes')]
class NoteController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        $notes = $em->getRepository(Note::class)->findAll();

        $data = array_map(fn($note) => [
            'id' => $note->getId(),
            'title' => $note->getTitle(),
            'content' => $note->getContent(),
            'createdAt' => $note->getCreatedAt()->format('Y-m-d H:i:s'),
        ], $notes);

        return new JsonResponse($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(EntityManagerInterface $em, int $id): JsonResponse
    {
        $note = $em->getRepository(Note::class)->find($id);

        if (!$note) {
            return new JsonResponse(['error' => 'Note not found'], 404);
        }

        return new JsonResponse([
            'id' => $note->getId(),
            'title' => $note->getTitle(),
            'content' => $note->getContent(),
            'createdAt' => $note->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $note = new Note();
        $note->setTitle($data['title']);
        $note->setContent($data['content']);
        $note->setCreatedAt(new \DateTimeImmutable());

        $em->persist($note);
        $em->flush();

        return new JsonResponse(['message' => 'Note created!'], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Request $request, EntityManagerInterface $em, int $id): JsonResponse
    {
        $note = $em->getRepository(Note::class)->find($id);

        if (!$note) {
            return new JsonResponse(['error' => 'Note not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) $note->setTitle($data['title']);
        if (isset($data['content'])) $note->setContent($data['content']);

        $em->flush();

        return new JsonResponse(['message' => 'Note updated']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $em, int $id): JsonResponse
    {
        $note = $em->getRepository(Note::class)->find($id);

        if (!$note) {
            return new JsonResponse(['error' => 'Note not found'], 404);
        }

        $em->remove($note);
        $em->flush();

        return new JsonResponse(['message' => 'Note deleted']);
    }
}
