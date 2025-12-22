# Game Icon Fetcher Setup

## Overview

This command automatically fetches game icons from multiple sources in priority order:
1. **Twitch** (most reliable for game box art)
2. **Steam** (fallback)
3. **Epic Games** (fallback, not yet implemented)

## Running the Command

Simply run:

```bash
docker-compose exec php php bin/console app:fetch-game-icons
```

## What It Does

1. Loops through all games in your database
2. For games without an image:
   - **First tries Twitch**: Uses `twitch_category` to fetch box art from Twitch CDN
   - **Then tries Steam**: Uses `steam_link` to fetch images from Steam CDN
   - **Then tries Epic**: (Not yet implemented)
   - Downloads the first available image
   - Resizes to 256x256 (square, center-cropped)
   - Converts to base64 and stores in the database
3. Shows progress with:
   - ✓ Updated count
   - Skipped count (games that already have images)
   - Failed count (games without any image source)

## Source Priority

### Why Twitch First?
- Twitch box art is consistently high quality
- Most modern games have Twitch categories
- Reliable CDN with good uptime
- Standard 600x800 resolution

### Steam Fallback
- Used when Twitch category isn't available
- Tries multiple image types (header, library art, capsule)
- Good for Steam-exclusive games

## Requirements

- No API key needed - all sources use public CDNs
- Automatic rate limiting (0.25s between requests)
- Games with `twitch_category` will be prioritized
- Games with `steam_link` used as fallback

## Example Output

```
Fetching Game Icons from Steam
===============================

Processing: Resident Evil 2 (2019)
  ✓ Found on Twitch
  Image URL (Twitch): https://static-cdn.jtvnw.net/ttv-boxart/...
✓ Updated: Resident Evil 2 (2019)

Processing: Some Obscure Game
  ✓ Found on Steam
  Image URL (Steam): https://cdn.cloudflare.steamstatic.com/...
✓ Updated: Some Obscure Game

...

[OK] Completed! Updated: 95, Skipped: 12, Failed: 3
```

## Troubleshooting

If a game fails:
- Check if `twitch_category` or `steam_link` is populated in the database
- Some older/obscure games might not have images on either CDN
- You can manually upload images through the admin panel

