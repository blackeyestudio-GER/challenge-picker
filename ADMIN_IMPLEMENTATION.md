# Admin Area Implementation Plan

## ✅ Completed

### Backend Setup
1. ✅ Added role helper methods to User entity:
   - `isAdmin()` - Check if user has ROLE_ADMIN
   - `isModerator()` - Check if user has ROLE_MOD or ROLE_ADMIN
   - `addRole(string $role)` - Add role to user
   - `removeRole(string $role)` - Remove role from user

2. ✅ Updated security.yaml:
   - Added `{ path: ^/api/admin, roles: ROLE_ADMIN }` rule

3. ✅ UserResponse DTO already includes `isAdmin` field
4. ✅ Frontend useAuth composable already has `isAdmin` in User interface

## 📋 To Implement

### Admin API Controllers

#### 1. Games Management (`/api/admin/games`)
**Endpoints:**
- `GET /api/admin/games` - List all games (with pagination)
- `GET /api/admin/games/{id}` - Get single game
- `POST /api/admin/games` - Create new game
- `PUT /api/admin/games/{id}` - Update game
- `DELETE /api/admin/games/{id}` - Delete game

**Fields:**
- name (string, required)
- description (text, optional)
- image (string/url, optional)
- is_category_representative (boolean)

#### 2. Rulesets Management (`/api/admin/rulesets`)
**Endpoints:**
- `GET /api/admin/rulesets` - List all rulesets
- `GET /api/admin/games/{gameId}/rulesets` - List rulesets for a game
- `GET /api/admin/rulesets/{id}` - Get single ruleset
- `POST /api/admin/rulesets` - Create new ruleset
- `PUT /api/admin/rulesets/{id}` - Update ruleset
- `DELETE /api/admin/rulesets/{id}` - Delete ruleset

**Fields:**
- name (string, required)
- description (text, optional)
- game_id (integer, required)
- is_default (boolean)

#### 3. Rules Management (`/api/admin/rules`)
**Endpoints:**
- `GET /api/admin/rules` - List all rules
- `GET /api/admin/rulesets/{rulesetId}/rules` - List rules for a ruleset
- `GET /api/admin/rules/{id}` - Get single rule
- `POST /api/admin/rules` - Create new rule
- `PUT /api/admin/rules/{id}` - Update rule
- `DELETE /api/admin/rules/{id}` - Delete rule

**Fields:**
- title (string, required)
- description (text, required)
- ruleset_id (integer, required)
- difficulty_level (integer, 1-5)
- tags (array of strings, optional)

### Admin Frontend Pages

#### 1. Admin Dashboard (`/admin`)
- Overview stats (total games, rulesets, rules, users)
- Quick links to management pages
- Recent activity

#### 2. Games Management (`/admin/games`)
- List all games in a table
- Search/filter games
- Create new game button
- Edit/Delete actions per row
- Modal or dedicated page for create/edit form

#### 3. Rulesets Management (`/admin/rulesets`)
- List all rulesets with game name
- Filter by game
- Create new ruleset button
- Edit/Delete actions
- Form to create/edit

#### 4. Rules Management (`/admin/rules`)
- List all rules with ruleset name
- Filter by ruleset/game
- Create new rule button
- Edit/Delete actions
- Form with rich text editor for description

### Middleware
Create `admin.ts` middleware in `/middleware/` folder:
```typescript
export default defineNuxtRouteMiddleware((to, from) => {
  const { user } = useAuth()
  
  if (!user.value || !user.value.isAdmin) {
    return navigateTo('/dashboard')
  }
})
```

### Component Structure
Create admin components in `/components/admin/`:
- `AdminNav.vue` - Admin navigation sidebar
- `GameForm.vue` - Create/Edit game form
- `RulesetForm.vue` - Create/Edit ruleset form
- `RuleForm.vue` - Create/Edit rule form
- `DeleteConfirmModal.vue` - Confirmation modal for deletions

### How to Set First Admin
Run SQL command in MySQL:
```sql
UPDATE users SET roles = '["ROLE_ADMIN"]' WHERE email = 'your-email@example.com';
```

Or create a Symfony command:
```bash
docker-compose exec php php bin/console app:user:promote <email> ROLE_ADMIN
```

## Implementation Priority

1. **Phase 1: Basic CRUD Controllers** (Start here)
   - Create AdminGameController with basic CRUD
   - Test with curl/Postman
   
2. **Phase 2: Complete Backend**
   - Add AdminRulesetController
   - Add AdminRuleController
   - Add validation and error handling
   
3. **Phase 3: Frontend Foundation**
   - Create admin middleware
   - Create admin layout
   - Add admin nav to AppHeader (if isAdmin)
   
4. **Phase 4: Admin UI**
   - Build admin dashboard
   - Build games management page
   - Build rulesets management page
   - Build rules management page

## Security Notes

- All admin routes protected by `ROLE_ADMIN` in security.yaml
- Frontend middleware prevents non-admins from accessing admin pages
- Always validate input on backend
- Log admin actions for audit trail (future enhancement)
- Consider adding CSRF protection for write operations

## Testing

1. Create test admin user:
   ```sql
   UPDATE users SET roles = '["ROLE_ADMIN"]' WHERE username = 'testadmin';
   ```

2. Test API endpoints with JWT token:
   ```bash
   curl -H "Authorization: Bearer YOUR_TOKEN" \
        http://localhost:8090/api/admin/games
   ```

3. Verify non-admin cannot access:
   ```bash
   # Should return 403 Forbidden
   curl -H "Authorization: Bearer NON_ADMIN_TOKEN" \
        http://localhost:8090/api/admin/games
   ```

