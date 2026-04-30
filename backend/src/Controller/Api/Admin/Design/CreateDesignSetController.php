<?php

namespace App\Controller\Api\Admin\Design;

use App\DTO\Request\Admin\CreateDesignSetRequest;
use App\DTO\Response\Admin\DesignSetListItem;
use App\DTO\Response\Admin\DesignSetMutationResponse;
use App\Entity\CardDesign;
use App\Entity\DesignSet;
use App\Repository\DesignNameRepository;
use App\Repository\TarotCardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/design-sets', name: 'api_admin_design_sets_create', methods: ['POST'])]
class CreateDesignSetController extends AbstractController
{
    public function __construct(
        private readonly DesignNameRepository $designNameRepository,
        private readonly TarotCardRepository $tarotCardRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $payloadData = $request->toArray();
            /** @var array<string, mixed> $payloadData */
            $payload = CreateDesignSetRequest::fromArray($payloadData);
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

            $designNameId = $payload->designNameId;
            if ($designNameId === null) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'designNameId is required',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            $designName = $this->designNameRepository->find($designNameId);
            if (!$designName) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'DESIGN_NAME_NOT_FOUND',
                        'message' => 'Design name not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            $designSet = new DesignSet();
            $designSet->setDesignName($designName);
            $designSet->setType($payload->type);
            $isFree = $payload->isFree;
            $designSet->setIsFree($isFree);
            $price = is_numeric($payload->price) ? (float) $payload->price : null;
            $isPremium = !$isFree && $price !== null && $price > 0;
            $designSet->setIsPremium($isPremium);
            $designSet->setPrice($price !== null ? (string) $price : null);
            $designSet->setTheme($payload->theme);
            $designSet->setDescription($payload->description);

            if ($designSet->isTemplate()) {
                $templates = [
                    ['identifier' => 'TEMPLATE_BASIC', 'type' => 'basic'],
                    ['identifier' => 'TEMPLATE_COURT', 'type' => 'court'],
                    ['identifier' => 'TEMPLATE_LEGENDARY', 'type' => 'legendary'],
                ];

                foreach ($templates as $template) {
                    $cardDesign = new CardDesign();
                    $cardDesign->setCardIdentifier($template['identifier']);
                    $cardDesign->setDesignSet($designSet);
                    $cardDesign->setIsTemplate(true);
                    $cardDesign->setTemplateType($template['type']);
                    $cardDesign->setImageBase64(null);

                    $this->entityManager->persist($cardDesign);
                    $designSet->addCardDesign($cardDesign);
                }

                $cardCount = 3;
                $message = 'Design set created successfully with 3 empty template slots';
            } else {
                $tarotCards = $this->tarotCardRepository->findAllOrdered();
                foreach ($tarotCards as $tarotCard) {
                    $cardDesign = new CardDesign();
                    $cardDesign->setCardIdentifier($tarotCard->getIdentifier());
                    $cardDesign->setDesignSet($designSet);
                    $cardDesign->setIsTemplate(false);
                    $cardDesign->setImageBase64(null);

                    $this->entityManager->persist($cardDesign);
                    $designSet->addCardDesign($cardDesign);
                }

                $cardCount = 78;
                $message = 'Design set created successfully with 78 empty card slots';
            }

            $this->entityManager->persist($designSet);
            $this->entityManager->flush();

            $designSetId = $designSet->getId();
            $designNameValue = $designName->getName();
            if ($designSetId === null) {
                throw new \RuntimeException('Design set is missing required data');
            }

            return $this->json(
                DesignSetMutationResponse::fromValues(
                    $message,
                    new DesignSetListItem(
                        id: $designSetId,
                        designNameId: $designNameId,
                        designName: $designNameValue,
                        type: $designSet->getType(),
                        isFree: $designSet->isFree(),
                        isPremium: $designSet->isPremium(),
                        price: $designSet->getPrice(),
                        theme: $designSet->getTheme(),
                        description: $designSet->getDescription(),
                        cardCount: $cardCount,
                        completedCards: 0,
                        isComplete: false,
                        previewImage: null,
                        previewImages: [],
                        createdAt: $designSet->getCreatedAt()->format('c'),
                        updatedAt: $designSet->getUpdatedAt()->format('c')
                    )
                ),
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            error_log('Failed to create design set: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'CREATE_FAILED',
                    'message' => 'Failed to create design set',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
