<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Update existing games with store links
 */
final class Version20251222215400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update existing games from Twitch list with store and category links';
    }

    public function up(Schema $schema): void
    {
        // Update games that already existed with their proper links
        $updates = [
            // Format: [name, steam, epic, gog, twitch]
            ['Counter-Strike 2', 'https://store.steampowered.com/app/730/', null, null, 'Counter-Strike 2'],
            ['Elden Ring', 'https://store.steampowered.com/app/1245620/', null, null, 'Elden Ring'],
            ['Resident Evil 4', 'https://store.steampowered.com/app/2050650/', null, null, 'Resident Evil 4'],
            ['Overwatch 2', 'https://store.steampowered.com/app/2357570/', null, null, 'Overwatch 2'],
            ['Grand Theft Auto V', 'https://store.steampowered.com/app/271590/', 'https://store.epicgames.com/en-US/p/grand-theft-auto-v', null, 'Grand Theft Auto V'],
            ['Red Dead Redemption 2', 'https://store.steampowered.com/app/1174180/', 'https://store.epicgames.com/en-US/p/red-dead-redemption-2', null, 'Red Dead Redemption 2'],
            ['Cyberpunk 2077', 'https://store.steampowered.com/app/1091500/', 'https://store.epicgames.com/en-US/p/cyberpunk-2077', 'https://www.gog.com/game/cyberpunk_2077', 'Cyberpunk 2077'],
            ['Minecraft', 'https://www.minecraft.net/en-us/store/minecraft-java-bedrock-edition-pc', null, null, 'Minecraft'],
            ['Fortnite', 'https://www.fortnite.com/', 'https://store.epicgames.com/en-US/p/fortnite', null, 'Fortnite'],
            ['League of Legends', 'https://www.leagueoflegends.com/', null, null, 'League of Legends'],
            ['Dota 2', 'https://store.steampowered.com/app/570/', null, null, 'Dota 2'],
            ['VALORANT', 'https://playvalorant.com/', null, null, 'VALORANT'],
            ['Apex Legends', 'https://store.steampowered.com/app/1172470/', null, null, 'Apex Legends'],
            ['Call of Duty: Warzone', 'https://www.callofduty.com/warzone', null, null, 'Call of Duty: Warzone'],
            ['Call of Duty: Modern Warfare III', 'https://store.steampowered.com/app/2519060/', null, null, 'Call of Duty: Modern Warfare III'],
            ["Baldur's Gate 3", 'https://store.steampowered.com/app/1086940/', 'https://store.epicgames.com/en-US/p/baldurs-gate-3', 'https://www.gog.com/game/baldurs_gate_3', "Baldur's Gate 3"],
            ['Hogwarts Legacy', 'https://store.steampowered.com/app/990080/', 'https://store.epicgames.com/en-US/p/hogwarts-legacy', null, 'Hogwarts Legacy'],
            ['Starfield', 'https://store.steampowered.com/app/1716740/', null, null, 'Starfield'],
            ['World of Warcraft', 'https://worldofwarcraft.blizzard.com/', null, null, 'World of Warcraft'],
            ['Final Fantasy XIV', 'https://store.steampowered.com/app/39210/', null, null, 'Final Fantasy XIV Online'],
            ['Lost Ark', 'https://store.steampowered.com/app/1599340/', null, null, 'Lost Ark'],
            ['Path of Exile', 'https://store.steampowered.com/app/238960/', 'https://store.epicgames.com/en-US/p/path-of-exile', null, 'Path of Exile'],
            ['Diablo IV', 'https://us.shop.battle.net/en-us/product/diablo-iv', null, null, 'Diablo IV'],
            ['Genshin Impact', 'https://genshin.hoyoverse.com/', 'https://store.epicgames.com/en-US/p/genshin-impact', null, 'Genshin Impact'],
            ['Honkai: Star Rail', 'https://hsr.hoyoverse.com/', 'https://store.epicgames.com/en-US/p/honkai-star-rail', null, 'Honkai: Star Rail'],
            ['Roblox', 'https://www.roblox.com/', null, null, 'Roblox'],
            ['Terraria', 'https://store.steampowered.com/app/105600/', 'https://store.epicgames.com/en-US/p/terraria', 'https://www.gog.com/game/terraria', 'Terraria'],
            ['Dead by Daylight', 'https://store.steampowered.com/app/381210/', 'https://store.epicgames.com/en-US/p/dead-by-daylight', null, 'Dead by Daylight'],
            ['Phasmophobia', 'https://store.steampowered.com/app/739630/', null, null, 'Phasmophobia'],
            ['Lethal Company', 'https://store.steampowered.com/app/1966720/', null, null, 'Lethal Company'],
            ['Resident Evil Village', 'https://store.steampowered.com/app/1196590/', null, null, 'Resident Evil Village'],
            ['God of War Ragnarök', 'https://store.steampowered.com/app/2322010/', 'https://store.epicgames.com/en-US/p/god-of-war-ragnarok', null, 'God of War Ragnarök'],
            ['The Witcher 3: Wild Hunt', 'https://store.steampowered.com/app/292030/', 'https://store.epicgames.com/en-US/p/the-witcher-3-wild-hunt', 'https://www.gog.com/game/the_witcher_3_wild_hunt_game_of_the_year_edition', 'The Witcher 3: Wild Hunt'],
            ['Sea of Thieves', 'https://store.steampowered.com/app/1172620/', null, null, 'Sea of Thieves'],
            ['Among Us', 'https://store.steampowered.com/app/945360/', 'https://store.epicgames.com/en-US/p/among-us', null, 'Among Us'],
            ['Fall Guys', 'https://store.steampowered.com/app/1097150/', 'https://store.epicgames.com/en-US/p/fall-guys', null, 'Fall Guys'],
            ['Rocket League', 'https://www.rocketleague.com/', 'https://store.epicgames.com/en-US/p/rocket-league', null, 'Rocket League'],
            ['EA Sports FC 24', 'https://www.ea.com/games/ea-sports-fc/fc-24', 'https://store.epicgames.com/en-US/p/ea-sports-fc-24', null, 'EA Sports FC 24'],
            ['NBA 2K24', 'https://store.steampowered.com/app/2338770/', 'https://store.epicgames.com/en-US/p/nba-2k24', null, 'NBA 2K24'],
            ['F1 23', 'https://store.steampowered.com/app/2108330/', null, null, 'F1 23'],
            ['Forza Horizon 5', 'https://store.steampowered.com/app/1551360/', null, null, 'Forza Horizon 5'],
            ['Street Fighter 6', 'https://store.steampowered.com/app/1364780/', null, null, 'Street Fighter 6'],
            ['Tekken 8', 'https://store.steampowered.com/app/1778820/', null, null, 'Tekken 8'],
            ['Mortal Kombat 1', 'https://store.steampowered.com/app/1971870/', 'https://store.epicgames.com/en-US/p/mortal-kombat-1', null, 'Mortal Kombat 1'],
            ['Rainbow Six Siege', 'https://store.steampowered.com/app/359550/', 'https://store.epicgames.com/en-US/p/rainbow-six-siege', null, "Tom Clancy's Rainbow Six Siege"],
            ['Rust', 'https://store.steampowered.com/app/252490/', null, null, 'Rust'],
            ['ARK: Survival Evolved', 'https://store.steampowered.com/app/346110/', 'https://store.epicgames.com/en-US/p/ark', null, 'ARK: Survival Evolved'],
            ['DayZ', 'https://store.steampowered.com/app/221100/', null, null, 'DayZ'],
            ['Escape from Tarkov', 'https://www.escapefromtarkov.com/', null, null, 'Escape from Tarkov'],
            ['Factorio', 'https://store.steampowered.com/app/427520/', null, 'https://www.gog.com/game/factorio', 'Factorio'],
            ['Satisfactory', 'https://store.steampowered.com/app/526870/', 'https://store.epicgames.com/en-US/p/satisfactory', null, 'Satisfactory'],
            ['Cities: Skylines II', 'https://store.steampowered.com/app/949230/', null, null, 'Cities: Skylines II'],
            ['Crusader Kings III', 'https://store.steampowered.com/app/1158310/', null, null, 'Crusader Kings III'],
        ];

        foreach ($updates as [$name, $steam, $epic, $gog, $twitch]) {
            $this->addSql(
                "UPDATE games SET 
                 steam_link = COALESCE(steam_link, ?),
                 epic_link = COALESCE(epic_link, ?),
                 gog_link = COALESCE(gog_link, ?),
                 twitch_category = COALESCE(twitch_category, ?)
                 WHERE name = ?",
                [$steam, $epic, $gog, $twitch, $name]
            );
        }
    }

    public function down(Schema $schema): void
    {
        // Cannot restore original state
    }
}

