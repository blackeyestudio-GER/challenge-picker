<?php

namespace App\Controller\Api\Admin\Game;

use App\DTO\Response\Game\GameResponse;
use App\Entity\Game;
use App\Service\ArrayTypeHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/games', name: 'api_admin_games_create', methods: ['POST'])]
class CreateAdminGameController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (!is_array($data)) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_REQUEST',
                        'message' => 'Invalid request body',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            /* @var array<string, mixed> $data */
            try {
                $name = ArrayTypeHelper::getString($data, 'name');
            } catch (\InvalidArgumentException $e) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'Name is required',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            $game = new Game();
            $game->setName($name);
            $game->setDescription(ArrayTypeHelper::tryGetString($data, 'description'));
            $game->setImage(ArrayTypeHelper::tryGetString($data, 'image'));
            $game->setIsCategoryRepresentative(ArrayTypeHelper::tryGetBool($data, 'isCategoryRepresentative') ?? false);
            $game->setSteamLink(ArrayTypeHelper::tryGetString($data, 'steamLink'));
            $game->setEpicLink(ArrayTypeHelper::tryGetString($data, 'epicLink'));
            $game->setGogLink(ArrayTypeHelper::tryGetString($data, 'gogLink'));
            $game->setTwitchCategory(ArrayTypeHelper::tryGetString($data, 'twitchCategory'));

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
