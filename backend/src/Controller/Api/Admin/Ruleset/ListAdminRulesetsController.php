<?php

namespace App\Controller\Api\Admin\Ruleset;

use App\DTO\Response\Ruleset\RulesetResponse;
use App\Repository\RulesetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/rulesets', name: 'api_admin_rulesets_list', methods: ['GET'])]
class ListAdminRulesetsController extends AbstractController
{
    public function __construct(
        private readonly RulesetRepository $rulesetRepository
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $gameId = $request->query->get('gameId');

        if ($gameId) {
            $rulesets = $this->rulesetRepository->findBy(['game' => $gameId], ['name' => 'ASC']);
        } else {
            $rulesets = $this->rulesetRepository->findAll();
        }

        $rulesetResponses = array_map(
            fn ($ruleset) => RulesetResponse::fromEntity($ruleset),
            $rulesets
        );

        return $this->json([
            'success' => true,
            'data' => ['rulesets' => $rulesetResponses],
        ], Response::HTTP_OK);
    }
}
