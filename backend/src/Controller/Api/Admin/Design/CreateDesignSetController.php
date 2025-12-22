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
    ) {}

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
                        'message' => 'Design name not found'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            // Create the design set
            $designSet = new DesignSet();
            $designSet->setDesignName($designName);

            // Create all 78 card designs (empty images) from tarot_cards table
            $tarotCards = $this->tarotCardRepository->findAllOrdered();
            foreach ($tarotCards as $tarotCard) {
                $cardDesign = new CardDesign();
                $cardDesign->setCardIdentifier($tarotCard->getIdentifier());
                $cardDesign->setDesignSet($designSet);
                $cardDesign->setImageBase64(null);
                
                $this->entityManager->persist($cardDesign);
                $designSet->addCardDesign($cardDesign);
            }

            $this->entityManager->persist($designSet);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Design set created successfully with 78 empty card slots',
                'data' => [
                    'designSet' => [
                        'id' => $designSet->getId(),
                        'designNameId' => $designName->getId(),
                        'designName' => $designName->getName(),
                        'cardCount' => 78,
                        'completedCards' => 0,
                        'createdAt' => $designSet->getCreatedAt()->format('c')
                    ]
                ]
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            error_log('Failed to create design set: ' . $e->getMessage());
            
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'CREATE_FAILED',
                    'message' => 'Failed to create design set'
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

