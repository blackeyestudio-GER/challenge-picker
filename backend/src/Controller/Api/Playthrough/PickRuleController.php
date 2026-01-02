<?php

namespace App\Controller\Api\Playthrough;

use App\Repository\PlaythroughRepository;
use App\Repository\RuleRepository;
use App\Service\PlaythroughRuleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PickRuleController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly RuleRepository $ruleRepository,
        private readonly PlaythroughRuleService $playthroughRuleService
    ) {
    }

    #[Route('/api/playthroughs/{uuid}/pick-rule', name: 'api_playthrough_pick_rule', methods: ['POST'])]
    public function __invoke(string $uuid, Request $request): JsonResponse
    {
        // Get authenticated user (optional - viewers can also pick if allowed)
        $user = $this->getUser();

        // Get playthrough
        $playthrough = $this->playthroughRepository->findByUuid($uuid);
        if (!$playthrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PLAYTHROUGH_NOT_FOUND',
                    'message' => 'Playthrough not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        // Check if session is active
        if ($playthrough->getStatus() !== 'active') {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'SESSION_NOT_ACTIVE',
                    'message' => 'Session must be active to pick rules',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Check permissions
        $isHost = $user && $playthrough->getUser()->getUuid()->equals($user->getUuid());
        if (!$isHost && !$playthrough->isAllowViewerPicks()) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'VIEWER_PICKS_DISABLED',
                    'message' => 'Viewers are not allowed to pick rules for this session',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        // Get rule ID from request
        $data = json_decode($request->getContent(), true);
        $ruleId = $data['ruleId'] ?? null;
        $difficultyLevel = $data['difficultyLevel'] ?? null;

        if (!$ruleId || !$difficultyLevel) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_REQUEST',
                    'message' => 'ruleId and difficultyLevel are required',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Verify rule exists
        $rule = $this->ruleRepository->find($ruleId);
        if (!$rule) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'RULE_NOT_FOUND',
                    'message' => 'Rule not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $playthroughRule = $this->playthroughRuleService->pickRule(
                $playthrough,
                $rule,
                $difficultyLevel
            );

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $playthroughRule->getId(),
                    'ruleId' => $rule->getId(),
                    'ruleName' => $rule->getName(),
                    'message' => 'Rule activated successfully',
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PICK_ERROR',
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
