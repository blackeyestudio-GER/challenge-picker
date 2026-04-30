<?php

namespace App\Controller\Api\Admin\Design;

use App\DTO\Request\Admin\CreateDesignNameRequest;
use App\DTO\Response\Admin\DesignNameItem;
use App\DTO\Response\Admin\DesignNameMutationResponse;
use App\Entity\DesignName;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/design-names', name: 'api_admin_design_names_create', methods: ['POST'])]
class CreateDesignNameController extends AbstractController
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
            $payload = CreateDesignNameRequest::fromArray($payloadData);
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

            $designName = new DesignName();
            $designName->setName($payload->name);
            $designName->setDescription($payload->description);

            $this->entityManager->persist($designName);
            $this->entityManager->flush();

            $designNameId = $designName->getId();
            $designNameValue = $designName->getName();
            if ($designNameId === null) {
                throw new \RuntimeException('Design name is missing required data');
            }

            return $this->json(
                DesignNameMutationResponse::fromValues(
                    'Design name created successfully',
                    new DesignNameItem(
                        id: $designNameId,
                        name: $designNameValue,
                        description: $designName->getDescription(),
                        createdAt: $designName->getCreatedAt()->format('c'),
                        designSetCount: 0
                    )
                ),
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            error_log('Failed to create design name: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'CREATE_FAILED',
                    'message' => 'Failed to create design name',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
