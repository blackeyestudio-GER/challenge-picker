# Challenge Picker Feature Set

This file is the canonical current-state feature inventory for the project.

It is meant to replace the split view across `ROADMAP.md`, `TODO.md`, `MISSING_FEATURES.md`, and older status files where entries have drifted over time.

Last reviewed: 2026-05-04

## Implemented Core Product Areas

### Authentication and user account
- Email/password registration
- Email/password login
- Discord OAuth login
- Discord account linking/unlinking
- Password reset flow
- Email verification flow
- JWT auth and refresh-token flow
- Profile update
- Password change
- Self-service account deletion with credential confirmation
- Delete-account UI confirmation gate (`DELETE` must be typed before the destructive action is enabled)
- Avatar upload
- Theme preference update

### Game, category, rule, and ruleset system
- Game CRUD
- Category CRUD
- Rule CRUD
- Ruleset CRUD
- Game-to-category assignment
- Category representative games
- Game favorites
- Ruleset favorites
- Ruleset voting
- Game category voting
- Ruleset inheritance/category-based availability
- Rule difficulty levels
- Tarot-card-based rule structure

### Playthrough system
- Create playthrough
- Setup/start/pause/resume/end playthrough
- Automatic discard of completed runs shorter than 3 minutes
- Max concurrent rules
- Rule picking
- Rule toggling during setup
- Viewer pick permissions
- Public/private run visibility
- Play dashboard
- Public run page
- Public browse-runs page
- Completed-runs list
- Completed-run detail page
- Add/update video URL
- Run feedback (`finishedRun`, `recommended`)
- Delete short completed runs under 3 minutes

### Challenge system
- Send challenge
- Accept challenge
- Respond to challenge
- Challenge details
- Challenge comparison
- Sent challenges view
- My challenges view

### OBS and streaming support
- OBS preferences
- User timer/play/rules/status endpoints
- Public play overlays
- Playthrough overlay page

### Admin system
- Admin dashboard
- Admin games
- Admin categories
- Admin rules
- Admin rulesets
- Admin icons
- Admin features
- Admin designs/design sets
- Admin shop settings
- Admin payouts
- Admin stats

### Shop and artist features
- Shop listing
- Checkout session creation
- Stripe webhook integration
- Purchases view
- Transactions view
- Retry failed transaction
- Artist apply flow
- Artist dashboard
- Earnings view
- Earnings history
- Payout request flow

## Still Missing or Incomplete

### High-priority product gaps
- Twitch is currently out of scope for the active product plan.
  The remaining Twitch code/UI surface should be treated as cleanup work, not as a release feature.
  Twitch account linking should stay hidden behind the Symfony feature flag until credentials and testing are available again.

### UX and consistency gaps
- Error handling is still inconsistent across the app.
  Some flows use the shared notification/error helpers properly, some still only log to console or show generic messages.

- Loading states are still inconsistent across the app.
  Core flows are mostly covered, but not all admin and utility screens are equally polished.

- Success notifications are still inconsistent.
  Many CRUD operations show them, but not all.

- Theme/design system adoption is incomplete.
  The major user-facing outliers were normalized, but the broader admin and utility surface still needs the same cleanup.

- The theme guardrail is now present, but still baseline-based.
  New raw palette drift can be blocked, but the existing allowlisted backlog still needs to be cleaned down over time.

- Admin and secondary pages are not fully normalized yet.
  The remaining work is mostly in legacy admin CRUD/detail screens, modal-heavy flows, and utility pages that still use older one-off styling patterns.

### Frontend/runtime polish
- Custom 404/500/error experiences are still incomplete.
- Accessibility has not had a systematic pass yet.
- Cross-browser verification is not documented as complete.
- The product is desktop-first.
  Tablet polish is optional future work; full phone/mobile support is not a current requirement and should not be treated as a release blocker.

### Documentation and release engineering
- There is no single complete API spec.
- There is no single complete admin guide.
- There is no single complete user guide.
- CI/CD pipeline foundation now exists, but it is not yet proven and documented as complete.
- Production monitoring/error tracking is not documented as finished.
- Backup/recovery strategy is not documented as finished.

### Testing gaps
- Backend PHPUnit is now present with initial lifecycle coverage.
- Automated checks now exist for generated API contracts and theme drift.
- Critical path coverage is still thin, and most end-to-end behavior still relies on manual verification.

## Known Technical Debt

### PHP/static analysis
- PHPStan is not fully green yet.
  The count was reduced significantly, but the backend is not fully clean.

- PHPMD is now installed and wired in, but the ruleset still needs real cleanup work before it can be treated as fully mature quality coverage.

### Frontend architecture
- Some pages still carry page-local data contracts and styling conventions that should be unified further.
- The theme system still depends on too many fallback overrides in `assets/css/themes/light.css`.
- The generated API-contract path exists, but it still covers only part of the frontend/backend boundary.
  Full backend-owned contract coverage and consistent frontend consumption are still active implementation work.

### Data/model cleanup
- DTO coverage is broad in the core playthrough/challenge/admin surface, but not yet complete as a single generated contract system across the whole API.
- Some older endpoints still rely on manual response shaping or local frontend interfaces instead of fully shared generated types.

## Recommended Next Steps

1. Finish stabilization work:
   - PHPStan cleanup
   - PHPMD cleanup
   - broader automated tests for critical flows

2. Finish UI consistency work:
   - admin/theme normalization
   - consistent loading/error/success states
    - accessibility pass

3. Finish DTO/client consolidation:
   - expand backend-owned generated contract coverage
   - remove remaining page-local contract drift
   - move remaining manual frontend response interfaces onto generated contract types

4. Finish release/ops readiness:
   - CI checks
   - monitoring/backups when they are actually introduced as maintained project concerns

## Notes

- Incomplete broad docs were intentionally removed instead of being kept as misleading project references.
- This file should be treated as the current source of truth and updated whenever a feature is materially added, removed, or re-scoped.
