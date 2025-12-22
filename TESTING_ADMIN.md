# Testing Admin System

## ✅ Pre-requisites

1. Backend and frontend containers running:
   ```bash
   docker-compose up -d
   ```

2. Have a user account registered

## 🔧 Setup Admin Access

### Step 1: Get Your Email
Make sure you know the email address you used to register.

### Step 2: Set Admin Role

```bash
docker-compose exec mysql mysql -u root -prootpassword challenge_picker_db \
  -e "UPDATE users SET roles = JSON_ARRAY('ROLE_ADMIN') WHERE email = 'YOUR_EMAIL@example.com';"
```

Replace `YOUR_EMAIL@example.com` with your actual email.

### Step 3: Verify Admin Role Was Set

```bash
docker-compose exec mysql mysql -u root -prootpassword challenge_picker_db \
  -e "SELECT email, username, roles FROM users WHERE email = 'YOUR_EMAIL@example.com';"
```

You should see:
```
+------------------------+----------+------------------+
| email                  | username | roles            |
+------------------------+----------+------------------+
| YOUR_EMAIL@example.com | yourname | ["ROLE_ADMIN"]   |
+------------------------+----------+------------------+
```

### Step 4: Refresh JWT Token
**IMPORTANT**: You must log out and log back in for the admin role to take effect!

1. Open the app at `http://localhost:3000`
2. Click on your username (top-right)
3. Click "Logout"
4. Log back in with your credentials

## 🧪 Testing Admin Features

### 1. Verify Admin Panel Link

After logging in:
1. Click on your username (top-right)
2. You should now see **"Admin Panel"** in the dropdown menu
3. If you don't see it, double-check steps 2-4 above

### 2. Test Games CRUD

#### Create a Game
1. Navigate to `/admin/games` or click "Admin Panel" → "Games"
2. Click "Create Game" button
3. Fill in the form:
   - **Name**: "Test Game"
   - **Description**: "This is a test game"
   - **Image URL**: (leave empty or add a URL)
   - **Category Representative**: (leave unchecked)
4. Click "Create"
5. Verify the game appears in the table

#### Edit a Game
1. Click "Edit" on the game you just created
2. Change the name to "Test Game (Edited)"
3. Click "Update"
4. Verify the name changed in the table

#### Delete a Game
1. Click "Delete" on the test game
2. Confirm the deletion
3. Verify the game is removed from the table

### 3. Test Rulesets CRUD

#### Create a Ruleset
1. Navigate to `/admin/rulesets` (use the tabs at the top)
2. Click "Create Ruleset"
3. Fill in the form:
   - **Game**: Select a game from dropdown
   - **Name**: "Test Ruleset"
   - **Description**: "Test description"
   - **Set as default**: (check or uncheck)
4. Click "Create"
5. Verify the ruleset appears in the table with game name

#### Edit a Ruleset
1. Click "Edit" on a ruleset
2. Note: Game dropdown is disabled (can't change game after creation)
3. Change the name to "Test Ruleset (Edited)"
4. Click "Update"
5. Verify the changes

#### Delete a Ruleset
1. Click "Delete" on the test ruleset
2. Confirm the deletion
3. Verify it's removed

### 4. Test Rules CRUD

#### Create a Rule
1. Navigate to `/admin/rules` (use the tabs)
2. Click "Create Rule"
3. Fill in the form:
   - **Ruleset**: Select a ruleset (shows "Game - Ruleset")
   - **Name**: "Test Rule"
   - **Description**: "This is a test rule"
4. Click "Create"
5. Verify the rule appears with ruleset name

#### Edit a Rule
1. Click "Edit" on a rule
2. Note: Ruleset dropdown is disabled (can't change ruleset after creation)
3. Change the description
4. Click "Update"
5. Verify the changes

#### Delete a Rule
1. Click "Delete" on a rule
2. Confirm deletion
3. Verify it's removed

## 🔍 Backend API Testing (Optional)

You can also test the API directly with curl:

### Get JWT Token
```bash
curl -X POST http://localhost:8090/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"YOUR_EMAIL@example.com","password":"YOUR_PASSWORD"}'
```

Save the `token` from the response.

### Test Admin Endpoint
```bash
TOKEN="your_jwt_token_here"

# List games
curl -X GET http://localhost:8090/api/admin/games \
  -H "Authorization: Bearer $TOKEN"

# Create game
curl -X POST http://localhost:8090/api/admin/games \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"API Test Game","description":"Created via API"}'
```

## ✅ Success Criteria

- [ ] Admin Panel link appears in dropdown for admin users
- [ ] Admin Panel link does NOT appear for regular users
- [ ] Can navigate between Games/Rulesets/Rules admin pages
- [ ] Can create new games/rulesets/rules
- [ ] Can edit existing games/rulesets/rules
- [ ] Can delete games/rulesets/rules
- [ ] Modals open and close correctly
- [ ] Form validation works (try submitting empty name)
- [ ] Table updates after CRUD operations
- [ ] Empty states show helpful messages
- [ ] Loading states appear during operations

## 🐛 Common Issues

### "Admin Panel" not showing
- Did you set `roles = JSON_ARRAY('ROLE_ADMIN')`?
- Did you log out and log back in after setting the role?
- Check browser console for errors

### 403 Forbidden on API calls
- Your JWT token doesn't have admin role
- Log out and log back in to get new token

### Modal not opening
- Check browser console for errors
- Try hard refresh (Cmd+Shift+R / Ctrl+Shift+R)

### Changes not saving
- Check backend logs: `docker-compose logs -f php`
- Check browser network tab for failed requests

## 📊 Database Verification

Check data was actually created:

```bash
# List all games
docker-compose exec mysql mysql -u root -prootpassword challenge_picker_db \
  -e "SELECT id, name, is_category_representative FROM games;"

# List all rulesets
docker-compose exec mysql mysql -u root -prootpassword challenge_picker_db \
  -e "SELECT id, name, game_id, is_default FROM rulesets;"

# List all rules
docker-compose exec mysql mysql -u root -prootpassword challenge_picker_db \
  -e "SELECT id, name, ruleset_id FROM rules;"
```

## 🎉 Next Steps

Once testing is complete:
1. Create some real games and rulesets for your application
2. Test the regular user flow (game selection, playthrough creation)
3. Consider adding more admin features (user management, etc.)

