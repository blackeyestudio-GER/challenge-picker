<?php

namespace App\Controller\Api\Admin\Game;

use App\DTO\Response\Game\GameResponse;
use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/games', name: 'api_admin_games_create', methods: ['POST'])]
class CreateAdminGameController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $game = new Game();
            $game->setName($data['name']);
            $game->setDescription($data['description'] ?? null);
            $game->setImage($data['image'] ?? null);
            $game->setIsCategoryRepresentative($data['isCategoryRepresentative'] ?? false);
            $game->setSteamLink($data['steamLink'] ?? null);
            $game->setEpicLink($data['epicLink'] ?? null);
            $game->setGogLink($data['gogLink'] ?? null);
            $game->setTwitchCategory($data['twitchCategory'] ?? null);

            $this->entityManager->persist($game);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Game created successfully',
                'data' => ['game' => GameResponse::fromEntity($game)],
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            error_log('Failed to create game: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'CREATE_FAILED',
                    'message' => 'Failed to create game',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
