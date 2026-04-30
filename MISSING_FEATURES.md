# Missing Features Report
**Generated:** January 26, 2026

## 🔴 Critical Missing Features (For v1.0 Launch)

### Authentication
1. **Twitch OAuth Login** ❌
   - **Status:** Only account linking exists (for logged-in users)
   - **Missing:** Login flow for new/existing users via Twitch
   - **Evidence:** No Twitch login on sign-in page; linking is optional via env
   - **Backend:** `TwitchCallbackController` has login logic but incomplete (line 116-123)
   - **Priority:** 🔴 Critical

2. **Account Deletion** ❌
   - **Status:** Not implemented
   - **Missing:** Self-service account deletion endpoint
   - **Priority:** 🟡 Medium (from roadmap)

**Note:** Password Reset and Email Verification ARE fully implemented (both backend and frontend)

**Layout / devices:** The product is **desktop-first** (play surface, OBS, admin). **Full mobile responsiveness is not a v1.0 goal** and is intentionally out of scope here. A **tablet-friendly pass** may be considered later if needed; phone layouts are not required.

### Frontend/UX
3. **Comprehensive Error Handling** ⚠️
   - **Status:** Partial
   - **Missing:** Consistent error messages across all forms and API failures
   - **Priority:** 🔴 Critical

4. **Success Notifications** ⚠️
   - **Status:** Partial
   - **Missing:** Success notifications for all CRUD operations
   - **Priority:** 🟡 Medium

5. **Loading States** ⚠️
   - **Status:** Partial
   - **Missing:** Loading indicators for all API calls
   - **Priority:** 🟡 High

6. **404 / Error Pages** ❌
   - **Status:** Not implemented
   - **Missing:** Custom error pages
   - **Priority:** 🟡 Medium

7. **Accessibility Improvements** ❌
   - **Status:** Not implemented
   - **Missing:** WCAG 2.1 AA compliance
   - **Priority:** 🟡 Medium

## 🟡 Important Missing Features (Not Blocking)

### Card Display System (From TODO.md)
8. **TarotCard Component** ❌
   - **Status:** Not built
   - **Missing:** Core component to display rule cards
   - **Location:** Should be `components/TarotCard.vue`
   - **Priority:** 🟡 High

9. **Card Design Service Frontend** ✅
    - **Status:** Implemented
    - **Location:** `composables/useCardDesign.ts`
    - **Features:** Fetches card designs with fallback logic, template detection
    - **Priority:** 🟡 High

10. **Card Integration into Player Pages** ❌
    - **Status:** Not implemented
    - **Missing:** Cards displayed in:
      - `/playthrough/dashboard` - Active rule cards
      - `/play/[uuid]/rules` - Rule deck
      - `/games/[id]/rulesets` - Ruleset preview
    - **Priority:** 🟡 High

### Icon System
11. **Icon Upload API** ❌
    - **Status:** Not implemented
    - **Missing:** `backend/src/Controller/Api/Admin/Icon/CreateRuleIconController.php`
    - **Missing:** `components/modal/IconUploadModal.vue`
    - **Priority:** 🟢 Low

12. **Icon Assignment** ⚠️
    - **Status:** 57% complete (134/236 rules have icons)
    - **Missing:** 102 rules still need icons assigned
    - **Priority:** 🟢 Low (can be done iteratively)

### User Features
13. **User Statistics** ✅
    - **Status:** Implemented
    - **Backend:** `backend/src/Controller/Api/User/GetUserStatsController.php` (enhanced)
    - **Frontend:** `composables/useUserStats.ts` + dashboard display
    - **Features:** Completed playthroughs, rules played, total active rules, votes cast
    - **Priority:** 🟡 High

14. **Ruleset Templates** ✅
    - **Status:** Implemented
    - **Backend:** `isTemplate` field on Ruleset entity, `ListTemplateRulesetsController.php`
    - **Frontend:** `composables/useRulesetTemplates.ts` + UI in rulesets page
    - **Features:** Browse and use pre-made popular rulesets
    - **Migration:** `backend/migrations/Version20260126000000.php` (needs to be run)
    - **Priority:** 🟡 High

15. **Game Icons Auto-fetch** ✅
    - **Status:** Already implemented
    - **Location:** `backend/src/Command/FetchGameIconsCommand.php`
    - **Features:** Fetches from Twitch, Steam CDN (when Steam link provided)
    - **Command:** `make fetch-icons` or `php bin/console app:fetch-game-icons`
    - **Priority:** 🟡 High

## 🟢 Nice-to-Have Missing Features

16. **Export Playthrough** ❌
    - **Status:** Not implemented
    - **Missing:** Download session data
    - **Priority:** 🟢 Low

17. **Share to Social** ❌
    - **Status:** Not implemented
    - **Missing:** Tweet/Discord share buttons
    - **Priority:** 🟢 Low

18. **Ruleset Comments** ❌
    - **Status:** Not implemented
    - **Missing:** Community feedback on rulesets
    - **Priority:** 🟢 Low

19. **Card Animations** ❌
    - **Status:** Not implemented
    - **Missing:** Flip, pulse, shuffle animations
    - **Priority:** 🟢 Low

20. **Rule Card Deck View** ❌
    - **Status:** Not implemented
    - **Missing:** Fanned card layout, drag to reorder
    - **Priority:** 🟢 Low

21. **Icon Management** ❌
    - **Status:** Not implemented
    - **Missing:** Edit metadata, delete unused, bulk upload, usage stats
    - **Priority:** 🟢 Low

22. **Design Set Preview** ❌
    - **Status:** Not implemented
    - **Missing:** Multi-card preview in admin, auto-generate samples
    - **Priority:** 🟢 Low

23. **Advanced Card Customization** ❌
    - **Status:** Not implemented
    - **Missing:** Per-rule color overrides, custom fonts, border styles, holographic effects
    - **Priority:** 🟢 Low

## ✅ Features That ARE Implemented (But May Be Incomplete)

### Authentication
- ✅ Email/password registration & login
- ✅ Discord OAuth (login + account linking)
- ✅ Twitch OAuth (account linking only - login missing)
- ✅ Password reset (fully implemented: backend + frontend `/auth/reset-password`)
- ✅ Email verification (fully implemented: backend + frontend `/auth/verify-email`)
- ✅ Refresh token system (30-day "Remember Me")

### Core Features
- ✅ Playthrough system (create, start, pause, resume, end)
- ✅ OBS integration (timer, rules, status overlays)
- ✅ Admin panel (complete)
- ✅ Shop system (Stripe integration)
- ✅ Game & ruleset management
- ✅ Rule system with variants
- ✅ Card design management
- ✅ Icon system (90 icons, customization)

## 📊 Summary by Category

| Category | Missing | Partial | Complete |
|----------|---------|---------|----------|
| **Critical for Launch** | 2 | 3 | - |
| **Important** | 7 | 0 | - |
| **Nice-to-Have** | 8 | 0 | - |
| **Total Missing** | **17** | **3** | - |

## 🎯 Recommended Priority Order

### Week 1: Critical Launch Blockers
1. Twitch OAuth login (complete the callback flow)
2. Comprehensive error handling
3. Loading states (complete coverage)

### Week 2: Important Features
4. TarotCard component (core card display)
5. Card design service frontend
6. Integrate cards into player pages
7. User statistics

### Week 3: Polish
8. Success notifications (complete)
9. 404/error pages
10. Accessibility improvements
11. Account deletion

### Week 4+: Nice-to-Have
12. Card animations
13. Icon upload
14. Social sharing
15. Export playthrough

## 📝 Notes

- **Password Reset & Email Verification:** These ARE implemented (controllers exist), but may need frontend integration testing
- **Card Display System:** This is a major missing piece from TODO.md - the entire card display infrastructure is not built
- **Twitch Login:** Backend has partial implementation but needs completion (JWT generation in callback)
- **Layout:** Desktop-first; full mobile layouts are **not** a launch requirement (optional tablet polish later)
