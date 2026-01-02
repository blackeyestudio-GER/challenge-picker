<?php

namespace App\Controller\Api\Admin\Design;

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

#[Route('/api/admin/design-sets', name: 'api_admin_design_sets_create', methods: ['POST'])]
class CreateDesignSetController extends AbstractController
{
    public function __construct(
        private readonly DesignNameRepository $designNameRepository,
        private readonly TarotCardRepository $tarotCardRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $designName = $this->designNameRepository->find($data['designNameId']);
            if (!$designName) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'DESIGN_NAME_NOT_FOUND',
                        'message' => 'Design name not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            // Create the design set
            $designSet = new DesignSet();
            $designSet->setDesignName($designName);
            $designSet->setType($data['type'] ?? 'full');
            $designSet->setIsFree($data['isFree'] ?? true);
            // isPremium is derived: if not free and has a price, it's premium
            $isPremium = !($data['isFree'] ?? true) && !empty($data['price']) && (float) $data['price'] > 0;
            $designSet->setIsPremium($isPremium);
            $designSet->setPrice($data['price'] ?? null);
            $designSet->setTheme($data['theme'] ?? null);
            $designSet->setDescription($data['description'] ?? null);

            // Create card designs based on type
            if ($designSet->isTemplate()) {
                // Create 3 templates for template sets
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
                // Create all 78 card designs (empty images) from tarot_cards table
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

            return $this->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'designSet' => [
                        'id' => $designSet->getId(),
                        'designNameId' => $designName->getId(),
                        'designName' => $designName->getName(),
                        'type' => $designSet->getType(),
                        'isFree' => $designSet->isFree(),
                        'isPremium' => $designSet->isPremium(),
                        'price' => $designSet->getPrice(),
                        'theme' => $designSet->getTheme(),
                        'description' => $designSet->getDescription(),
                        'cardCount' => $cardCount,
                        'completedCards' => 0,
                        'createdAt' => $designSet->getCreatedAt()->format('c'),
                    ],
                ],
            ], Response::HTTP_CREATED);

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
