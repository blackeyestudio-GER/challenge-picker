<?php

namespace App\Controller\Api\Admin\Game;

use App\DTO\Request\Admin\CreateGameRequest;
use App\DTO\Response\Admin\GameMutationResponse;
use App\DTO\Response\Game\GameResponse;
use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/games', name: 'api_admin_games_create', methods: ['POST'])]
class CreateAdminGameController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $payloadData = $request->toArray();
            /** @var array<string, mixed> $payloadData */
            $payload = CreateGameRequest::fromArray($payloadData);
            $errors = $this->validator->validate($payload);

            if (count($errors) > 0) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => (string) $errors,
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            $game = new Game();
            $game->setName($payload->name);
            $game->setDescription($payload->description);
            $game->setImage($payload->image);
            $game->setIsCategoryRepresentative($payload->isCategoryRepresentative);
            $game->setSteamLink($payload->steamLink);
            $game->setEpicLink($payload->epicLink);
            $game->setGogLink($payload->gogLink);
            $game->setTwitchCategory($payload->twitchCategory);

            $this->entityManager->persist($game);
            $this->entityManager->flush();

            return $this->json(
                GameMutationResponse::fromValues('Game created successfully', GameResponse::fromEntity($game)),
                Response::HTTP_CREATED
            );

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
