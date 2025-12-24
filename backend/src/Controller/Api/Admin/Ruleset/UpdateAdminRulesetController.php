<?php

namespace App\Controller\Api\Admin\Ruleset;

use App\DTO\Response\Ruleset\RulesetResponse;
use App\Repository\RulesetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/rulesets/{id}', name: 'api_admin_rulesets_update', methods: ['PUT'])]
class UpdateAdminRulesetController extends AbstractController
{
    public function __construct(
        private readonly RulesetRepository $rulesetRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(int $id, Request $request): JsonResponse
    {
        try {
            $ruleset = $this->rulesetRepository->find($id);

            if (!$ruleset) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'RULESET_NOT_FOUND',
                        'message' => 'Ruleset not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['name'])) {
                $ruleset->setName($data['name']);
            }
            if (array_key_exists('description', $data)) {
                $ruleset->setDescription($data['description']);
            }
            if (isset($data['isDefault'])) {
                $ruleset->setIsDefault($data['isDefault']);
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Ruleset updated successfully',
                'data' => ['ruleset' => RulesetResponse::fromEntity($ruleset)],
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log('Failed to update ruleset: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Failed to update ruleset',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
