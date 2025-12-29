<?php

namespace App\DataFixtures;

use App\Entity\Game;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CategoryRepresentativeGameFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $representativeGames = [
            [
                'name' => 'Shooter',
                'description' => 'Generic shooter game for category ruleset inheritance',
                'category' => CategoryFixtures::CATEGORY_SHOOTER,
            ],
            [
                'name' => 'Horror',
                'description' => 'Generic horror game for category ruleset inheritance',
                'category' => CategoryFixtures::CATEGORY_HORROR,
            ],
            [
                'name' => 'RPG',
                'description' => 'Generic RPG game for category ruleset inheritance',
                'category' => CategoryFixtures::CATEGORY_RPG,
            ],
            [
                'name' => 'Roguelike',
                'description' => 'Generic roguelike game for category ruleset inheritance',
                'category' => CategoryFixtures::CATEGORY_ROGUELIKE,
            ],
            [
                'name' => 'MOBA',
                'description' => 'Generic MOBA game for category ruleset inheritance',
                'category' => CategoryFixtures::CATEGORY_MOBA,
            ],
            [
                'name' => 'Strategy',
                'description' => 'Generic strategy game for category ruleset inheritance',
                'category' => CategoryFixtures::CATEGORY_STRATEGY,
            ],
            [
                'name' => 'Platformer',
                'description' => 'Generic platformer game for category ruleset inheritance',
                'category' => CategoryFixtures::CATEGORY_PLATFORMER,
            ],
            [
                'name' => 'Retro Shooter',
                'description' => 'Generic retro shooter game for category ruleset inheritance',
                'category' => CategoryFixtures::CATEGORY_RETRO_SHOOTER,
            ],
            [
                'name' => 'Survival',
                'description' => 'Generic survival game for category ruleset inheritance',
                'category' => CategoryFixtures::CATEGORY_SURVIVAL,
            ],
            [
                'name' => 'Fighting',
                'description' => 'Generic fighting game for category ruleset inheritance',
                'category' => CategoryFixtures::CATEGORY_FIGHTING,
            ],
            [
                'name' => 'Battle Royale',
                'description' => 'Generic battle royale game for category ruleset inheritance',
                'category' => CategoryFixtures::CATEGORY_BATTLE_ROYALE,
            ],
            [
                'name' => 'Action',
                'description' => 'Generic action game for category ruleset inheritance',
                'category' => CategoryFixtures::CATEGORY_ACTION,
            ],
            [
                'name' => 'Adventure',
                'description' => 'Generic adventure game for category ruleset inheritance',
                'category' => CategoryFixtures::CATEGORY_ADVENTURE,
            ],
            [
                'name' => 'Action-Adventure',
                'description' => 'Generic action-adventure game for category ruleset inheritance',
                'category' => CategoryFixtures::CATEGORY_ACTION_ADVENTURE,
            ],
            [
                'name' => 'Souls-like',
                'description' => 'Generic souls-like game for category ruleset inheritance',
                'category' => CategoryFixtures::CATEGORY_SOULSLIKE,
            ],
            [
                'name' => 'Metroidvania',
                'description' => 'Generic metroidvania game for category ruleset inheritance',
                'category' => CategoryFixtures::CATEGORY_METROIDVANIA,
            ],
            [
                'name' => 'Open World',
                'description' => 'Generic open world game for category ruleset inheritance',
                'category' => CategoryFixtures::CATEGORY_OPEN_WORLD,
            ],
        ];

        foreach ($representativeGames as $data) {
            $game = new Game();
            $game->setName($data['name']);
            $game->setDescription($data['description']);
            $game->setIsCategoryRepresentative(true);
            $game->setIsActive(true);

            // Add the category
            /** @var \App\Entity\Category $category */
            $category = $this->getReference($data['category'], \App\Entity\Category::class);
            $game->addCategory($category);

            $manager->persist($game);

            // Set this game as the category's representative (ensures database constraint)
            $category->setRepresentativeGame($game);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }
}
