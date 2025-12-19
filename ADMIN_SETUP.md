# Admin Setup Guide

## Promoting a User to Admin

To grant admin access to a user (required for game management), use the following command:

### Docker Environment

```bash
docker-compose exec php php bin/console app:user:promote user@example.com
```

### Non-Docker Environment

```bash
php bin/console app:user:promote user@example.com
```

## Admin Capabilities

Users with the `ROLE_ADMIN` role can:
- **Manage Games**: Create, edit, and update game entries
- **Upload Game Images**: Add game cover art (auto-resized to 256x256px)
- Access the `/games/manage` page (hidden from regular users)

## Regular User Capabilities

Regular users (without admin role) can:
- Create and manage their own playthrough sessions
- Configure OBS browser sources
- Edit their profile
- View and play games

## Checking Admin Status

Admin users will see an additional "Manage Games" card on their dashboard. This card is automatically hidden for non-admin users.

## Security

- The backend validates admin role for all game management endpoints
- Frontend route `/games/manage` is protected by the `admin` middleware
- Non-admin users attempting to access admin endpoints will receive a `403 Forbidden` response

