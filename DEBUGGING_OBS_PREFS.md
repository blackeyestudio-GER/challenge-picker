# Debugging OBS Preferences Loading Issue

## Changes Made

### 1. Added Error Display
- OBS preferences page now shows error messages if loading fails
- Added a "Retry" button to attempt loading again

### 2. Added Console Logging
- `useObsPreferences.ts` now logs:
  - The URL being fetched
  - The auth headers being sent
  - The response received
  - Any errors that occur

### 3. Improved Error Handling
- Errors no longer throw and break the flow
- Error state is properly set and displayed to the user

## How to Debug

### Step 1: Open Browser Console
1. Go to `/obs-sources` page
2. Open browser DevTools (F12 or Cmd+Option+I)
3. Look at the Console tab

### Step 2: Check Console Output
You should see logs like:
```
Loading OBS preferences...
Fetching OBS preferences from: http://localhost:3000/api/users/me/obs-preferences
Auth headers: { Authorization: "Bearer eyJ..." }
OBS preferences response: { success: true, data: {...} }
Preferences loaded: {...}
```

### Step 3: Common Issues & Solutions

#### Issue: "Authorization" header is empty or missing
**Problem:** User not authenticated or token not loaded
**Solution:** 
- Make sure you're logged in
- Check if `localStorage` has `auth_token` and `auth_user`
- Try logging out and back in

#### Issue: 404 Not Found
**Problem:** Route not configured correctly
**Solution:**
- Check if Symfony routes are loaded: `docker-compose exec php php bin/console debug:router | grep obs`
- Restart backend: `docker-compose restart php nginx`

#### Issue: 401 Unauthorized
**Problem:** Token invalid or expired
**Solution:**
- Clear localStorage and log in again
- Check token format in console logs

#### Issue: Network error / CORS
**Problem:** Proxy not configured correctly
**Solution:**
- Check `nuxt.config.ts` proxy configuration
- Restart frontend: `docker-compose restart frontend`

### Step 4: Manual API Test

Test the endpoint directly:

```bash
# Get your token from browser localStorage
TOKEN="your_token_here"

# Test the endpoint
curl -H "Authorization: Bearer $TOKEN" \
     http://localhost:8090/api/users/me/obs-preferences
```

Expected response:
```json
{
  "success": true,
  "data": {
    "showTimerInSetup": true,
    "showTimerInActive": true,
    "showTimerInPaused": true,
    "showTimerInCompleted": true,
    "showRulesInSetup": false,
    "showRulesInActive": true,
    "showRulesInPaused": true,
    "showRulesInCompleted": false,
    "showStatusInSetup": false,
    "showStatusInActive": true,
    "showStatusInPaused": true,
    "showStatusInCompleted": false,
    "timerPosition": "on_card",
    "timerDesign": "numbers",
    "statusDesign": "word",
    "rulesDesign": "list",
    "chromaKeyColor": "#00FF00"
  }
}
```

## What to Look For

1. **In Console Logs:**
   - Is the fetch URL correct?
   - Is the Authorization header present?
   - What's the error message?

2. **In Network Tab:**
   - Status code (200, 401, 404, 500)?
   - Response body
   - Request headers

3. **On Page:**
   - Does it show "Loading preferences..."?
   - Does it show an error message?
   - Or does nothing show up?

## Quick Fix Checklist

- [ ] User is logged in
- [ ] Auth token is in localStorage
- [ ] Backend is running (`docker-compose ps`)
- [ ] Frontend is running and proxy is configured
- [ ] Database migration ran (`user_obs_preferences` table exists)
- [ ] Routes are loaded (check `debug:router`)

## Report Back

When asking for help, please provide:
1. Console logs from the browser
2. Network tab response (status code + body)
3. Any error messages displayed on the page

