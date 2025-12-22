<?php

namespace App\Controller\Api\Admin\Ruleset;

use App\DTO\Response\Ruleset\RulesetResponse;
use App\Entity\Ruleset;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/rulesets', name: 'api_admin_rulesets_create', methods: ['POST'])]
class CreateAdminRulesetController extends AbstractController
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $game = $this->gameRepository->find($data['gameId']);
            if (!$game) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'GAME_NOT_FOUND',
                        'message' => 'Game not found'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            $ruleset = new Ruleset();
            $ruleset->setName($data['name']);
            $ruleset->setDescription($data['description'] ?? null);
            $ruleset->setGame($game);
            $ruleset->setIsDefault($data['isDefault'] ?? false);

            $this->entityManager->persist($ruleset);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Ruleset created successfully',
                'data' => ['ruleset' => RulesetResponse::fromEntity($ruleset)]
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            error_log('Failed to create ruleset: ' . $e->getMessage());
            
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'CREATE_FAILED',
                    'message' => 'Failed to create ruleset'
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

