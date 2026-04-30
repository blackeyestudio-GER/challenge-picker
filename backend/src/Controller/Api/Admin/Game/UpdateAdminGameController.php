<?php

namespace App\Controller\Api\Admin\Game;

use App\DTO\Request\Admin\UpdateGameRequest;
use App\DTO\Response\Admin\GameMutationResponse;
use App\DTO\Response\Game\GameResponse;
use App\Repository\CategoryRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/games/{id}', name: 'api_admin_games_update', methods: ['PUT'])]
class UpdateAdminGameController extends AbstractController
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(int $id, Request $request): JsonResponse
    {
        try {
            $game = $this->gameRepository->find($id);

            if (!$game) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'GAME_NOT_FOUND',
                        'message' => 'Game not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            $payloadData = $request->toArray();
            /** @var array<string, mixed> $payloadData */
            $payload = UpdateGameRequest::fromArray($payloadData);
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

            if ($payload->name !== null) {
                $game->setName($payload->name);
            }
            if ($payload->hasDescription) {
                $game->setDescription($payload->description);
            }
            if ($payload->hasImage) {
                $game->setImage($payload->image);
            }
            if ($payload->isCategoryRepresentative !== null) {
                $game->setIsCategoryRepresentative($payload->isCategoryRepresentative);
            }
            if ($payload->hasSteamLink) {
                $game->setSteamLink($payload->steamLink);
            }
            if ($payload->hasEpicLink) {
                $game->setEpicLink($payload->epicLink);
            }
            if ($payload->hasGogLink) {
                $game->setGogLink($payload->gogLink);
            }
            if ($payload->hasTwitchCategory) {
                $game->setTwitchCategory($payload->twitchCategory);
            }

            if ($payload->hasCategoryIds) {
                foreach ($game->getCategories() as $category) {
                    $game->removeCategory($category);
                }

                foreach ($payload->categoryIds ?? [] as $categoryId) {
                    $category = $this->categoryRepository->find($categoryId);
                    if ($category) {
                        $game->addCategory($category);
                    }
                }
            }

            $this->entityManager->flush();

            return $this->json(
                GameMutationResponse::fromValues('Game updated successfully', GameResponse::fromEntity($game)),
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            error_log('Failed to update game: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Failed to update game',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
