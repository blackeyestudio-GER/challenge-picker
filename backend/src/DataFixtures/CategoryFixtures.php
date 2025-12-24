<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public const CATEGORY_SHOOTER = 'category_shooter';
    public const CATEGORY_BATTLE_ROYALE = 'category_battle_royale';
    public const CATEGORY_MOBA = 'category_moba';
    public const CATEGORY_RPG = 'category_rpg';
    public const CATEGORY_HORROR = 'category_horror';
    public const CATEGORY_ROGUELIKE = 'category_roguelike';
    public const CATEGORY_PLATFORMER = 'category_platformer';
    public const CATEGORY_FIGHTING = 'category_fighting';
    public const CATEGORY_SURVIVAL = 'category_survival';
    public const CATEGORY_RETRO_SHOOTER = 'category_retro_shooter';

    public function load(ObjectManager $manager): void
    {
        $categories = [
            [
                'name' => 'Shooter',
                'slug' => 'shooter',
                'description' => 'First-person and third-person shooter games',
                'kick_category' => 'Shooter',
                'reference' => self::CATEGORY_SHOOTER,
            ],
            [
                'name' => 'Battle Royale',
                'slug' => 'battle-royale',
                'description' => 'Last-player-standing competitive games',
                'kick_category' => 'Battle Royale',
                'reference' => self::CATEGORY_BATTLE_ROYALE,
            ],
            [
                'name' => 'MOBA',
                'slug' => 'moba',
                'description' => 'Multiplayer Online Battle Arena games',
                'kick_category' => 'MOBA',
                'reference' => self::CATEGORY_MOBA,
            ],
            [
                'name' => 'RPG',
                'slug' => 'rpg',
                'description' => 'Role-Playing Games with character progression',
                'kick_category' => 'RPG',
                'reference' => self::CATEGORY_RPG,
            ],
            [
                'name' => 'Horror',
                'slug' => 'horror',
                'description' => 'Survival horror and scary games',
                'kick_category' => 'Horror',
                'reference' => self::CATEGORY_HORROR,
            ],
            [
                'name' => 'Roguelike',
                'slug' => 'roguelike',
                'description' => 'Procedurally generated dungeon crawlers',
                'kick_category' => 'Roguelike',
                'reference' => self::CATEGORY_ROGUELIKE,
            ],
            [
                'name' => 'Platformer',
                'slug' => 'platformer',
                'description' => 'Platform jumping and exploration games',
                'kick_category' => 'Platformer',
                'reference' => self::CATEGORY_PLATFORMER,
            ],
            [
                'name' => 'Fighting',
                'slug' => 'fighting',
                'description' => 'One-on-one combat games',
                'kick_category' => 'Fighting',
                'reference' => self::CATEGORY_FIGHTING,
            ],
            [
                'name' => 'Survival',
                'slug' => 'survival',
                'description' => 'Resource management and survival games',
                'kick_category' => 'Survival',
                'reference' => self::CATEGORY_SURVIVAL,
            ],
            [
                'name' => 'Retro Shooter',
                'slug' => 'retro-shooter',
                'description' => 'Classic and retro-style first-person shooters',
                'kick_category' => 'FPS',
                'reference' => self::CATEGORY_RETRO_SHOOTER,
            ],
        ];

        // Load images if available
        $images = [];
        if (class_exists('App\\DataFixtures\\CategoryImagesData')) {
            $images = CategoryImagesData::getImages();
        }

        foreach ($categories as $data) {
            $category = new Category();
            $category->setName($data['name']);
            $category->setSlug($data['slug']);
            $category->setDescription($data['description']);
            $category->setKickCategory($data['kick_category'] ?? null);

            // Set image if available
            if (isset($images[$data['name']])) {
                $category->setImage($images[$data['name']]);
            }

            $manager->persist($category);
            $this->addReference($data['reference'], $category);
        }

        $manager->flush();
    }
}
