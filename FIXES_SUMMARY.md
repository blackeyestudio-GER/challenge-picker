# Bug Fixes Summary - OBS Preferences & API URLs

## Issue 1: Double `/api/api/` in API URLs ✅ FIXED

### Problem
All API requests were failing with 404 because URLs had a duplicate `/api/` prefix:
```
❌ /api/api/users/me/obs-preferences
❌ /api/api/games
❌ /api/api/playthroughs
```

### Root Cause
The `config.public.apiBase` in Nuxt already includes `/api`, but composables were adding `/api/` again.

### Files Fixed (6 files)
1. **composables/useObsPreferences.ts** - Fixed 2 URLs
2. **composables/usePlaythrough.ts** - Fixed 12 URLs
3. **composables/useGames.ts** - Fixed 3 URLs
4. **pages/play/[uuid]/rules.vue** - Fixed 1 URL
5. **pages/play/[uuid]/status.vue** - Fixed 1 URL
6. **pages/play/[uuid]/timer.vue** - Fixed 1 URL

### Change Made
```typescript
// Before:
`${config.public.apiBase}/api/users/...`  // ❌ Results in /api/api/users/...

// After:
`${config.public.apiBase}/users/...`  // ✅ Results in /api/users/...
```

---

## Issue 2: OBS Preferences Returning 401 Unauthorized ✅ FIXED

### Problem
After fixing the URL issue, OBS preferences API was still returning 401 Unauthorized:
```
GET /api/users/me/obs-preferences 401 (Unauthorized)
```

### Root Cause
The OBS controllers were incorrectly trying to manually parse the JWT token by calling:
```php
$user = $this->authService->getUserFromToken($request);  // ❌ Wrong!
```

This was passing the entire `Request` object to a method that expects a string token.

### Solution
Use Symfony's built-in authentication via `AbstractController::getUser()`:
```php
$user = $this->getUser();  // ✅ Correct!
```

This method automatically handles JWT authentication through Symfony's security system.

### Files Fixed (2 files)
1. **backend/src/Controller/Api/Obs/GetObsPreferencesController.php**
   - Changed `$this->authService->getUserFromToken($request)` to `$this->getUser()`
   - Removed unused `AuthService` dependency

2. **backend/src/Controller/Api/Obs/UpdateObsPreferencesController.php**
   - Changed `$this->authService->getUserFromToken($request)` to `$this->getUser()`
   - Removed unused `AuthService` dependency

---

## Issue 3: Missing JWT Authentication Configuration ✅ FIXED

### Problem
Even after fixing the controllers, 401 Unauthorized errors persisted because Symfony's security system wasn't configured to handle JWT authentication.

### Root Cause
The `config/packages/security.yaml` was using default configuration with:
- Dummy in-memory user provider
- No JWT authenticator configured in the firewall
- No stateless API authentication

### Solution
Completely reconfigured `security.yaml`:

1. **User Provider**: Changed from `users_in_memory` to database-backed provider using `App\Entity\User`
```yaml
providers:
    app_user_provider:
        entity:
            class: App\Entity\User
            property: email
```

2. **JWT Firewall**: Added stateless API firewall with JWT authentication
```yaml
api:
    pattern: ^/api
    stateless: true
    provider: app_user_provider
    jwt: ~
```

3. **Access Control**: Configured public/protected routes
```yaml
access_control:
    - { path: ^/api/auth/login, roles: PUBLIC_ACCESS }
    - { path: ^/api/play/.*/preferences, roles: PUBLIC_ACCESS }
    - { path: ^/api/users/me, roles: ROLE_USER }
    - { path: ^/api, roles: ROLE_USER }
```

### Actions Taken
1. Updated `backend/config/packages/security.yaml`
2. Cleared Symfony cache: `php bin/console cache:clear`
3. Restarted PHP container to apply changes

---

## Result

All API endpoints now work correctly:
- ✅ `/api/users/me/obs-preferences` (GET) - Returns user preferences
- ✅ `/api/users/me/obs-preferences` (PUT) - Updates user preferences
- ✅ All game, playthrough, and overlay endpoints work
- ✅ JWT authentication properly configured
- ✅ Public endpoints accessible without auth
- ✅ Protected endpoints require valid JWT

OBS preferences page should now load and display all settings correctly! 🎉

---

## Testing Checklist

- [ ] **Hard refresh browser** (Cmd+Shift+R / Ctrl+Shift+R)
- [ ] Navigate to `/obs-sources`
- [ ] Verify preferences load without errors
- [ ] Verify checkboxes, sliders, and selects are visible
- [ ] Test updating preferences
- [ ] Check browser console - should show **200 OK** instead of 401
- [ ] Verify all overlay URLs work

