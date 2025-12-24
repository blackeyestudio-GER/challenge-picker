<?php

namespace App\Controller\Api\Ruleset;

use App\Entity\User;
use App\Entity\UserFavoriteRuleset;
use App\Repository\RulesetRepository;
use App\Repository\UserFavoriteRulesetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/rulesets/{rulesetId}/favorite', name: 'api_ruleset_favorite', methods: ['POST'])]
class ToggleFavoriteRulesetController extends AbstractController
{
    public function __construct(
        private readonly RulesetRepository $rulesetRepository,
        private readonly UserFavoriteRulesetRepository $favoriteRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(
        int $rulesetId,
        #[CurrentUser] User $user
    ): JsonResponse {
        try {
            $ruleset = $this->rulesetRepository->find($rulesetId);

            if (!$ruleset) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'RULESET_NOT_FOUND',
                        'message' => 'Ruleset not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            $existingFavorite = $this->favoriteRepository->findFavorite($user, $ruleset);

            if ($existingFavorite) {
                // Remove favorite
                $this->entityManager->remove($existingFavorite);
                $this->entityManager->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Ruleset removed from favorites',
                    'data' => [
                        'isFavorited' => false,
                    ],
                ], Response::HTTP_OK);
            } else {
                // Add favorite
                $favorite = new UserFavoriteRuleset();
                $favorite->setUser($user);
                $favorite->setRuleset($ruleset);

                $this->entityManager->persist($favorite);
                $this->entityManager->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Ruleset added to favorites',
                    'data' => [
                        'isFavorited' => true,
                    ],
                ], Response::HTTP_OK);
            }

        } catch (\Exception $e) {
            error_log('Toggle favorite ruleset failed: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FAVORITE_FAILED',
                    'message' => 'Failed to toggle favorite',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
