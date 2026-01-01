<?php

namespace App\Controller\Api\Playthrough;

use App\DTO\Request\Playthrough\UpdateRuleFeedbackRequest;
use App\DTO\Response\Playthrough\PlaythroughResponse;
use App\Entity\Playthrough;
use App\Repository\PlaythroughRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/playthroughs/{uuid}/rule-feedback', name: 'api_playthrough_rule_feedback', methods: ['PUT'])]
#[IsGranted('ROLE_USER')]
class UpdateRuleFeedbackController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(string $uuid, UpdateRuleFeedbackRequest $request): JsonResponse
    {
        // Validate request
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => implode(', ', $errorMessages),
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        $playthrough = $this->playthroughRepository->findOneBy(['uuid' => $uuid]);
        if (!$playthrough) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Playthrough not found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        // Verify ownership
        if (!$playthrough->getUser()->getUuid()->equals($user->getUuid())) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'You can only update your own playthroughs',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        // Only allow feedback on completed playthroughs
        if ($playthrough->getStatus() !== Playthrough::STATUS_COMPLETED) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_STATUS',
                    'message' => 'Rule feedback can only be set on completed playthroughs',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Update configuration with rule feedback
            $configuration = $playthrough->getConfiguration();
            $rules = $configuration['rules'] ?? [];

            // Find and update the rule
            $ruleFound = false;
            foreach ($rules as &$rule) {
                if (isset($rule['id']) && $rule['id'] === $request->ruleId) {
                    $rule['couldBeHarder'] = $request->couldBeHarder;
                    $ruleFound = true;
                    break;
                }
            }

            // If rule not found, add it
            if (!$ruleFound) {
                $rules[] = [
                    'id' => $request->ruleId,
                    'couldBeHarder' => $request->couldBeHarder,
                ];
            }

            $configuration['rules'] = $rules;
            $playthrough->setConfiguration($configuration);

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'data' => PlaythroughResponse::fromEntity($playthrough),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Failed to update rule feedback: ' . $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
