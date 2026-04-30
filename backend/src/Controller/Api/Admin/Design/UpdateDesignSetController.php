<?php

namespace App\Controller\Api\Admin\Design;

use App\Repository\DesignSetRepository;
use App\Service\ArrayTypeHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/design-sets/{id}', name: 'api_admin_design_sets_update', methods: ['PUT', 'PATCH'])]
class UpdateDesignSetController extends AbstractController
{
    public function __construct(
        private readonly DesignSetRepository $designSetRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(int $id, Request $request): JsonResponse
    {
        try {
            $designSet = $this->designSetRepository->find($id);
            if (!$designSet) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'DESIGN_SET_NOT_FOUND',
                        'message' => 'Design set not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

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

            /** @var array<string, mixed> $data */
            // Validate that type is not being changed (templates have 3 cards, full sets have 78)
            if (isset($data['type'])) {
                $type = ArrayTypeHelper::getString($data, 'type');
                if ($type !== $designSet->getType()) {
                    return $this->json([
                        'success' => false,
                        'error' => [
                            'code' => 'TYPE_IMMUTABLE',
                            'message' => 'Design set type cannot be changed after creation (templates have 3 cards, full sets have 78)',
                        ],
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            // Update editable fields (only type is immutable)
            $designName = $designSet->getDesignName();
            if (isset($data['name']) && $designName) {
                $designName->setName(ArrayTypeHelper::getString($data, 'name'));
            }

            if (isset($data['description'])) {
                $designSet->setDescription(ArrayTypeHelper::tryGetString($data, 'description'));
            }

            if (isset($data['theme'])) {
                $designSet->setTheme(ArrayTypeHelper::tryGetString($data, 'theme'));
            }

            if (isset($data['isFree'])) {
                $designSet->setIsFree(ArrayTypeHelper::getBool($data, 'isFree'));
            }

            if (isset($data['price'])) {
                $price = ArrayTypeHelper::tryGetFloat($data, 'price');
                $designSet->setPrice($price);
            }

            // Auto-calculate isPremium: not free AND has a price > 0
            $isFree = isset($data['isFree']) ? ArrayTypeHelper::getBool($data, 'isFree') : $designSet->isFree();
            $price = isset($data['price']) ? ArrayTypeHelper::tryGetFloat($data, 'price') : $designSet->getPrice();
            $isPremium = !$isFree && $price !== null && $price > 0;
            $designSet->setIsPremium($isPremium);

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Design set updated successfully',
                'data' => [
                    'designSet' => [
                        'id' => $designSet->getId(),
                        'designNameId' => $designName?->getId(),
                        'designName' => $designName?->getName(),
                        'type' => $designSet->getType(),
                        'isFree' => $designSet->isFree(),
                        'isPremium' => $designSet->isPremium(),
                        'price' => $designSet->getPrice(),
                        'theme' => $designSet->getTheme(),
                        'description' => $designSet->getDescription(),
                        'createdAt' => $designSet->getCreatedAt()->format('c'),
                        'updatedAt' => $designSet->getUpdatedAt()->format('c'),
                    ],
                ],
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log('Failed to update design set: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UPDATE_FAILED',
                    'message' => 'Failed to update design set',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
