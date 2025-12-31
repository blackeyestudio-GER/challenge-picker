<?php

namespace App\Controller\Api\Admin\Icon;

use App\Repository\RuleIconRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/rule-icons', name: 'api_admin_rule_icons_list', methods: ['GET'])]
class ListRuleIconsController extends AbstractController
{
    public function __construct(
        private readonly RuleIconRepository $ruleIconRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        try {
            $icons = $this->ruleIconRepository->findAll();

            $data = [];
            foreach ($icons as $icon) {
                $data[] = [
                    'id' => $icon->getId(),
                    'identifier' => $icon->getIdentifier(),
                    'category' => $icon->getCategory(),
                    'displayName' => $icon->getDisplayName(),
                    'svgContent' => $icon->getSvgContent(),
                    'tags' => $icon->getTags(),
                    'color' => $icon->getColor(),
                    'license' => $icon->getLicense(),
                    'source' => $icon->getSource(),
                    'createdAt' => $icon->getCreatedAt()->format('c'),
                    'updatedAt' => $icon->getUpdatedAt()->format('c'),
                ];
            }

            return $this->json([
                'success' => true,
                'data' => ['icons' => $data],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FETCH_FAILED',
                    'message' => 'Failed to fetch rule icons',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
