# 💰 Automated Artist Payout System

## Overview

Artist payouts are now **fully automated** via cron job. The system automatically creates payout requests for artists when their available balance reaches the minimum threshold ($10).

## How It Works

### Automated Process

1. **Cron Job Runs** (daily/weekly as configured)
   - Executes: `app:process-artist-payouts` command
   - Scans all artists in the system

2. **For Each Artist**:
   - Calculates total earnings from `designer_earnings` table
   - Subtracts any pending payout requests
   - If available balance ≥ $10 AND no pending request exists:
     - Creates automated `PayoutRequest` with status `pending`
     - Marks as `isAutomated = true`

3. **Admin Approval**:
   - Admin reviews pending payouts at `/admin/payouts`
   - Approves or rejects each payout request
   - When approved, admin processes actual payment (Stripe, PayPal, etc.)

### Manual Requests (Deprecated)

- Manual payout requests are **disabled** in the frontend
- The API endpoint still exists for backward compatibility but should not be used
- All payouts should be automated via cron job

## Setup

### Development

**Test the command**:
```bash
# Dry run (see what would be processed without creating requests)
make process-payouts-dry-run

# Actually process payouts
make process-payouts
```

### Production

**Add to crontab** (runs daily at 2 AM):
```bash
0 2 * * * cd /path/to/project && docker-compose exec -T php php bin/console app:process-artist-payouts >> /var/log/payouts.log 2>&1
```

**Or weekly** (every Monday at 2 AM):
```bash
0 2 * * 1 cd /path/to/project && docker-compose exec -T php php bin/console app:process-artist-payouts >> /var/log/payouts.log 2>&1
```

## Configuration

### Minimum Payout Amount

Currently set to **$10.00** in `ProcessArtistPayoutsCommand.php`:
```php
private const MINIMUM_PAYOUT_AMOUNT = '10.00';
```

To change: Modify the constant and redeploy.

### Currency

Default currency is **USD**. Can be configured per artist in the future if needed.

## Command Options

### Dry Run Mode

Test what would be processed without creating requests:
```bash
php bin/console app:process-artist-payouts --dry-run
```

### Output

The command provides:
- Progress bar for each artist
- Summary: Processed, Skipped, Errors
- Detailed logs for each payout created

## Database Schema

### `payout_requests` Table

- `id` - Primary key
- `designer_uuid` - FK to users (the artist)
- `amount` - Payout amount (DECIMAL 10,2)
- `currency` - Currency code (default: USD)
- `status` - pending/approved/rejected/paid
- `isAutomated` - Boolean flag (true for cron-generated, false for manual)
- `requestedAt` - When request was created
- `processedAt` - When admin approved/rejected
- `adminNotes` - Admin notes (required for rejection)
- `processedBy` - FK to users (admin who processed)

## Workflow

```
┌─────────────────┐
│  Cron Job Runs  │
│  (Daily/Weekly) │
└────────┬────────┘
         │
         ▼
┌─────────────────────────┐
│ Find All Artists        │
│ Calculate Available     │
│ Balance (Earnings -     │
│ Pending Payouts)        │
└────────┬────────────────┘
         │
         ▼
    ┌────────┐
    │ ≥ $10? │
    └───┬────┘
        │
    Yes │ No
        │   │
        │   └──► Skip
        │
        ▼
┌─────────────────────────┐
│ Pending Request         │
│ Already Exists?         │
└────────┬────────────────┘
         │
    Yes  │  No
    │    │
    └───►└───► Create Automated
              Payout Request
              (status: pending)
```

## Admin Interface

### `/admin/payouts`

- Lists all pending payout requests
- Shows "Automated" badge for cron-generated requests
- Shows "Manual" badge for manually created requests (if any)
- Admin can:
  - Approve payout (status → approved)
  - Reject payout (status → rejected, requires notes)
  - Add admin notes

## Artist Dashboard

### `/artist/dashboard`

- Shows total earnings, sales, and available balance
- Displays automated payout status:
  - "Next payout will be processed automatically when cron job runs" (if balance ≥ $10)
  - "Need $X more to reach minimum payout threshold" (if balance < $10)
  - "Payout request pending admin approval" (if pending request exists)
- Shows payout history with "Automated" badges

## Benefits

✅ **No Manual Work** - Artists don't need to remember to request payouts
✅ **Consistent Schedule** - Payouts processed on regular schedule
✅ **Fair Distribution** - All artists processed equally
✅ **Admin Control** - Admin still approves/rejects each payout
✅ **Audit Trail** - All payouts tracked with `isAutomated` flag

## Future Enhancements

- **Scheduled Frequency**: Configurable per artist (weekly/monthly)
- **Multiple Currencies**: Support for EUR, GBP, etc.
- **Payment Integration**: Auto-process approved payouts via Stripe Connect
- **Email Notifications**: Notify artists when payout is created/approved
- **Payout Limits**: Maximum payout amount per period

---

**Status**: ✅ **FULLY AUTOMATED** - Payouts are processed automatically via cron job!
