# 🛍️ Shop Purchase Tracking System

## ✅ What's Been Implemented

### Complete Purchase History & Transaction Tracking

Now you can see **everything** about what users have bought and attempted to buy!

---

## 📊 What We Track

### 1. **Completed Purchases** (`user_design_sets` table)
✅ What the user successfully bought and owns
- User UUID
- Design Set
- Purchase Date
- Price Paid (or `$0.00` for gifted items)
- Currency

### 2. **All Transaction Attempts** (`shop_transactions` table)
✅ Every purchase attempt, including:
- **Status**: `pending`, `completed`, `failed`, `refunded`, `cancelled`
- Stripe Session ID
- Items (JSON) - what they wanted to buy
- Amount & Currency
- Created Date & Completed Date

---

## 🔨 New Backend APIs

### User APIs

**`GET /api/shop/my-purchases`** - View completed purchases
- Returns all owned design sets with purchase details
- Sorted by newest first

**`GET /api/shop/my-transactions`** - View full transaction history
- Returns all transactions (pending, failed, completed, refunded, cancelled)
- Includes items attempted to purchase
- Sorted by newest first

**`POST /api/shop/retry-transaction/{id}`** - Retry a failed purchase
- Creates new Stripe checkout session
- Marks old transaction as `cancelled`
- Redirects to Stripe checkout

### Admin APIs

**`POST /api/admin/shop/gift-design-set`** - Gift a design set to a user
```json
{
  "userIdentifier": "user@example.com",  // Email or Discord ID
  "designSetId": 1
}
```
- Checks if user already owns it
- Creates ownership record with `$0.00` price
- Perfect for promotions, rewards, or customer support

---

## 🎨 New Frontend Pages

### `/shop/purchases` - My Purchases Page

**Two Tabs:**

1. **Completed Purchases**
   - Grid of owned design sets
   - Shows purchase date, price paid, theme
   - "Free / Gift" badge for gifted items

2. **Transaction History**
   - All transactions with status badges
   - Color-coded: green (completed), yellow (pending), red (failed), gray (refunded/cancelled)
   - **Retry button** for failed/pending transactions

**Features:**
- Clean, modern UI matching the shop aesthetic
- Back button to shop
- Badge showing count of retryable transactions
- Empty states for no purchases/transactions

---

## 🎯 Use Cases

### For Users:
✅ **View purchase history** - "What did I buy and when?"
✅ **Retry failed payments** - Card declined? Try again with one click
✅ **Track pending purchases** - See purchases awaiting confirmation

### For Admins:
✅ **Gift design sets** - Reward loyal users or resolve support issues
✅ **No duplicate gifts** - Automatically prevents gifting already-owned items
✅ **Transparent pricing** - Gifts show as `$0.00` to distinguish from purchases

### For You (Business Insights):
✅ **Conversion tracking** - See failed vs. completed purchases
✅ **Abandoned carts** - Track pending transactions
✅ **Refund tracking** - Full history of refunded transactions
✅ **Gift tracking** - Know which items were gifted vs. purchased

---

## 🚀 How to Use

### As a User:

1. Go to `/shop`
2. Click **"My Purchases"** button (top right)
3. View your owned designs and transaction history
4. Click **"Retry"** on any failed purchase to try again

### As an Admin:

**Gift a design set via API:**

```bash
curl -X POST http://localhost:8090/api/admin/shop/gift-design-set \
  -H "Authorization: Bearer YOUR_ADMIN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "userIdentifier": "user@example.com",
    "designSetId": 2
  }'
```

---

## 📈 Database Schema

### `user_design_sets` (Ownership)
```sql
id                INT AUTO_INCREMENT
user_uuid         BINARY(16)          -- UUID of user
design_set_id     INT                 -- FK to design_sets
purchased_at      DATETIME            -- Purchase timestamp
price_paid        DECIMAL(10,2)       -- Amount paid (0.00 for gifts)
currency          VARCHAR(3)          -- USD, EUR, etc.
```

### `shop_transactions` (Transaction History)
```sql
id                      INT AUTO_INCREMENT
user_uuid               BINARY(16)          -- UUID of user
stripe_session_id       VARCHAR(255) UNIQUE -- Stripe session
stripe_payment_intent_id VARCHAR(255)       -- Stripe payment intent
status                  VARCHAR(20)         -- pending/completed/failed/refunded/cancelled
amount                  DECIMAL(10,2)       -- Transaction amount
currency                VARCHAR(3)          -- USD, EUR, etc.
items                   JSON                -- [{design_set_id, quantity, price}]
created_at              DATETIME            -- When initiated
completed_at            DATETIME NULL       -- When completed
```

---

## 🔍 Transaction Status Flow

```
pending → completed  ✅ (Webhook received, payment successful)
pending → failed     ❌ (Payment declined, expired, cancelled by user)
completed → refunded 💸 (Refund issued)
pending → cancelled  🔄 (User retried, created new session)
```

---

## 🎁 Gifting Workflow

1. Admin calls `/api/admin/shop/gift-design-set`
2. System checks if user already owns it
3. If not, creates `UserDesignSet` record with:
   - `price_paid` = `0.00`
   - `currency` = `USD`
   - Current timestamp
4. User immediately owns the design set
5. Shows as "Free / Gift" in purchase history

---

## 🛡️ Security & Validation

✅ **User ownership verification** - Users can only retry their own transactions
✅ **Admin-only gifting** - Protected by admin middleware
✅ **Duplicate prevention** - Can't gift already-owned designs
✅ **Type safety** - PHPStan Level Max enforcement
✅ **Status validation** - Only failed/pending transactions can be retried

---

## 🎨 UI/UX Highlights

- **Status badges** - Color-coded for quick scanning
- **Empty states** - Encouraging messaging for first-time users
- **Retry CTA** - Red badge showing retryable transaction count
- **Responsive design** - Works on mobile, tablet, desktop
- **Loading states** - Spinner during data fetch and checkout redirect
- **Back navigation** - Easy return to shop

---

## 📝 Testing

### Test the User Flow:

1. **Make a purchase** - Go to `/shop`, buy a premium design
2. **View purchase** - Click "My Purchases" → See it in "Completed Purchases"
3. **View transaction** - Switch to "Transaction History" → See completed transaction

### Test Failed Purchase Retry:

1. **Use declining test card**: `4000 0000 0000 0002`
2. **View transactions** - See failed status with retry button
3. **Click retry** - New checkout session created
4. **Use successful card**: `4242 4242 4242 4242`
5. **Verify** - New transaction completed, old one marked cancelled

### Test Admin Gifting:

```bash
# Gift a design to a user
curl -X POST http://localhost:8090/api/admin/shop/gift-design-set \
  -H "Authorization: Bearer ADMIN_JWT" \
  -H "Content-Type: application/json" \
  -d '{"userIdentifier": "test@example.com", "designSetId": 1}'

# User logs in and sees it in "My Purchases" with "Free / Gift" badge
```

---

## 🎉 Benefits

### For Users:
- 🔍 **Transparency** - See exactly what they own and when they bought it
- 🔄 **Recovery** - Easily retry failed purchases
- 🎁 **Gifts visible** - Know when something was gifted vs. purchased

### For You:
- 📊 **Business Intelligence** - Track purchase patterns and failures
- 🎁 **Customer Support** - Gift designs to resolve issues or reward users
- 💰 **Revenue Tracking** - Distinguish paid vs. gifted items
- 🔄 **Conversion Optimization** - See where users drop off

---

## 🚀 Next Steps (Optional Enhancements)

Future additions you might want:
- **Admin dashboard** - Visual interface for gifting
- **Bulk gifting** - Gift to multiple users at once
- **Purchase receipts** - Email confirmation with PDF
- **Refund UI** - Admin interface for processing refunds
- **Analytics dashboard** - Charts for conversion rates, revenue, etc.

---

**Everything is ready to go!** Users can now track their purchases, retry failed payments, and you can gift designs for promotions or support. 🎉

