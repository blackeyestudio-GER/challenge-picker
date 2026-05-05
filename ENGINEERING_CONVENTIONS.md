# Engineering Conventions

Last reviewed: 2026-05-05

This file describes the target engineering model for the app outside the OBS/embed pages.

## 1. Frontend structure

### Pages
- Pages should be thin orchestration layers.
- Page state and workflow logic belongs in dedicated `composables/pages/useXxxPage.ts` files.
- Pages should mostly wire template bindings, route meta, and child components.

### Composables
- Shared domain/API logic belongs in `composables/useXxx.ts`.
- Page-specific controller logic belongs in `composables/pages/useXxxPage.ts`.
- Composables should expose typed refs, computed values, and handlers.

### Components
- Components should prefer presentation and bounded interaction over page orchestration.
- Shared loading/error/empty states should use common components.
- Repeated admin and user-facing interaction patterns should become reusable components before being copied again.

## 2. Styling and theming

### Default rule
- Visual semantics should come from theme tokens and shared component classes.
- Raw palette utilities should not be introduced in pages/components when a tokenized class or shared style is more appropriate.

### Tailwind usage
- Tailwind remains acceptable for layout, spacing, flex/grid, sizing, and simple structural utilities.
- Theme-sensitive color and surface decisions should move through the design system layer.

### Theme refactors
- Refactor toward reusable page/component classes, not one-off template styling.
- OBS/embed pages are allowed to remain more specialized and self-contained.

## 3. API contract model

### Source of truth
- Backend-defined contracts are the source of truth.
- Frontend types should come from generated contract artifacts, not page-local response interfaces.

### Current implementation
- Contracts are generated from `backend/contracts/frontend-api.json` into `generated/api-contracts.ts`.
- New active app-surface responses should be added there before introducing local response typing.

### Target direction
- The long-term direction is a standardized OpenAPI-based contract source.
- Until that migration is done, the existing generated contract path is the required consistency layer.

## 4. Backend API rules

### Requests
- Prefer request DTOs with `MapRequestPayload` instead of manual array parsing.

### Responses
- Prefer explicit response DTOs or a clearly standardized response object shape.
- Avoid ad-hoc JSON arrays from active app-surface controllers when a shared DTO exists or should exist.

## 5. Errors and user feedback

- Main product flows should not rely on `console.error` as the primary user-facing failure path.
- Frontend failures should surface through page state or notify helpers.
- Silent failure is only acceptable for clearly non-blocking background refresh behavior.

## 6. Scope exception

- OBS/embed pages are intentionally treated as a special integration surface.
- They may remain leaner and more specialized than the rest of the app as long as they stay stable and predictable for external browser-source usage.
