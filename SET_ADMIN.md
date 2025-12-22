# Set User as Admin

To grant admin privileges to a user, run this SQL command:

```bash
docker-compose exec mysql mysql -u root -prootpassword challenge_picker_db \
  -e "UPDATE users SET roles = JSON_ARRAY('ROLE_ADMIN') WHERE email = 'YOUR_EMAIL@example.com';"
```

Replace `YOUR_EMAIL@example.com` with your actual email address.

## Verify Admin Status

Check if admin role was applied:

```bash
docker-compose exec mysql mysql -u root -prootpassword challenge_picker_db \
  -e "SELECT uuid, email, username, roles FROM users WHERE email = 'YOUR_EMAIL@example.com';"
```

## Admin Features

Once you're an admin, you'll see:
- **Admin Panel** link in the dropdown menu (top right)
- Access to `/admin/games`, `/admin/rulesets`, and `/admin/rules` pages
- CRUD operations for:
  - Games
  - Rulesets
  - Rules

## Remove Admin Role

To remove admin privileges:

```bash
docker-compose exec mysql mysql -u root -prootpassword challenge_picker_db \
  -e "UPDATE users SET roles = JSON_ARRAY('ROLE_USER') WHERE email = 'YOUR_EMAIL@example.com';"
```

## Important Notes

- You must log out and log back in after changing roles for the changes to take effect
- The JWT token contains role information, so a new token must be generated

