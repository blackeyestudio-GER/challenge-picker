# Admin Promotion Commands

Quick reference for managing admin users in Challenge Picker.

## 📋 Available Commands

### `make admin-list`
Lists all users in the database with their admin status.

**Output:**
```
Available Users
===============

 ---------------- --------------------------- -------------------- ---------- 
  Username         Email                       Discord ID           Is Admin  
 ---------------- --------------------------- -------------------- ---------- 
  blackeye1987#0   arthur.kielbasa@gmail.com   139066162515410944   ✅ Yes    
 ---------------- --------------------------- -------------------- ---------- 
```

### `make admin-promote`
Interactive prompt to promote a user to admin.

**Usage:**
```bash
make admin-promote
# Then enter Discord ID or email when prompted
```

**Example:**
```bash
$ make admin-promote
========================================
  Promote User to Admin
========================================

Available users:

 ---------------- --------------------------- -------------------- ---------- 
  Username         Email                       Discord ID           Is Admin  
 ---------------- --------------------------- -------------------- ---------- 
  blackeye1987#0   arthur.kielbasa@gmail.com   139066162515410944   ❌ No     
 ---------------- --------------------------- -------------------- ---------- 

Enter Discord ID or email to promote: 139066162515410944

 [OK] ✅ User 'blackeye1987#0' (arthur.kielbasa@gmail.com) has been promoted to admin!
```

## 🔧 Direct Command Usage

If you prefer to use the Symfony console command directly:

```bash
# By Discord ID
docker-compose exec -T php php bin/console app:admin:promote 139066162515410944

# By email
docker-compose exec -T php php bin/console app:admin:promote arthur.kielbasa@gmail.com

# List users only
docker-compose exec -T php php bin/console app:admin:promote
```

## 📝 Common Scenarios

### After Database Reset
```bash
make db-reset          # Reset database and reload fixtures
make admin-promote     # Re-promote yourself to admin
```

### Quick Admin Access
```bash
make admin-list        # Find your Discord ID
make admin-promote     # Promote using Discord ID
```

### Promoting Team Members
```bash
# Option 1: Use their Discord ID
docker-compose exec -T php php bin/console app:admin:promote 123456789012345678

# Option 2: Use their email
docker-compose exec -T php php bin/console app:admin:promote teammate@example.com
```

## 🎯 Features

- ✅ **List all users** - See everyone in the database with admin status
- ✅ **Promote by Discord ID** - Use the unique Discord ID
- ✅ **Promote by email** - Use the user's email address
- ✅ **Already admin check** - Warns if user is already an admin
- ✅ **Pretty output** - Formatted tables with emoji indicators
- ✅ **Error handling** - Clear error messages if user not found

## 🔐 Security Notes

- Admin promotion requires direct server access (Docker/console)
- No API endpoint for promotion (security by design)
- Use `make admin-list` to verify changes
- Admin role persists in database across restarts

## 📂 Files Modified

- **Created:** `backend/src/Command/PromoteAdminCommand.php`
- **Updated:** `Makefile` (added `admin-list` and `admin-promote` targets)
- **Updated:** `.cursorrules` (documented in development tools)
- **Updated:** `README.md` (added to command reference)

