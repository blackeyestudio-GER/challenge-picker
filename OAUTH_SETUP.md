# OAuth Setup Instructions

## Discord & Twitch OAuth Configuration

### Step 1: Add Environment Variables

Create or edit `/backend/.env.local` and add these variables:

```env
# Discord OAuth
DISCORD_CLIENT_ID=1453167720123338924
DISCORD_CLIENT_SECRET=n_69XrZqAp99bkUtrjjkIgm4mF7JmT4l
DISCORD_REDIRECT_URI=http://localhost:8090/api/user/connect/discord/callback

# Twitch OAuth (Add these when you have them)
TWITCH_CLIENT_ID=your_twitch_client_id_here
TWITCH_CLIENT_SECRET=your_twitch_client_secret_here
TWITCH_REDIRECT_URI=http://localhost:8090/api/user/connect/twitch/callback
```

### Step 2: Configure Discord OAuth App

1. Go to https://discord.com/developers/applications
2. Select your application (ID: 1453167720123338924)
3. Go to "OAuth2" section
4. Add redirect URL: `http://localhost:8090/api/user/connect/discord/callback`
5. Save changes

### Step 3: Configure Twitch OAuth App (When Ready)

1. Go to https://dev.twitch.tv/console/apps
2. Create a new application or use existing one
3. Add OAuth Redirect URL: `http://localhost:8090/api/user/connect/twitch/callback`
4. Copy the Client ID and Client Secret
5. Add them to `.env.local` as shown above

### Step 4: Restart Backend

After adding the environment variables, restart your Docker container:

```bash
docker-compose restart php
```

## Features Implemented

✅ Database schema with Discord/Twitch fields
✅ Connect/Disconnect Discord endpoints
✅ Connect/Disconnect Twitch endpoints
✅ OAuth callback handlers for both platforms
✅ Profile page UI with connection status
✅ Real-time updates after successful connection
✅ Error handling and user feedback

## Testing

1. Log in to your account
2. Go to Profile page
3. Click "Connect" button for Discord or Twitch
4. Authorize in the popup window
5. Window will close and profile will update automatically

## Security Notes

- Never commit `.env.local` to git (it's already in .gitignore)
- OAuth state validation should be added for production
- Consider storing OAuth tokens in Redis for session management
- Add rate limiting to OAuth endpoints in production

