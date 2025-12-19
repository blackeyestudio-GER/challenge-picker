# Error Handling Improvements

## Problem
The error handling was too basic - just logging errors and showing raw error messages. This approach has issues:
- No retry logic for transient failures
- Raw technical errors shown to users
- No distinction between different error types
- Would become "useless" once everything works (no way to test it)

## Solution: Production-Ready Error Handling

### Features Implemented

#### 1. **Automatic Retry Logic** (GET requests only)
```typescript
fetchPreferences(retryCount = 0)
```
- Automatically retries server errors (5xx) and network failures
- Max 2 retries with exponential backoff (1s, 2s)
- Doesn't retry client errors (4xx) - those are user/config issues

#### 2. **Smart Error Classification**

**401 Unauthorized**
- Message: "Your session has expired. Please log in again."
- Action: Don't retry (user needs to re-authenticate)
- Common cause: Expired JWT token

**422/400 Validation Errors**
- Message: "Invalid preference values. Please check your input."
- Action: Show specific validation errors
- Common cause: Bad input data

**5xx Server Errors**
- Message: "Failed to load OBS preferences. Please try again."
- Action: Automatic retry with backoff
- Common cause: Temporary server issues

**Network Errors**
- Message: "Failed to load OBS preferences. Please try again."
- Action: Automatic retry
- Common cause: Connection issues, Docker restart

#### 3. **User-Friendly Messages**
- Users see clear, actionable error messages
- Technical details logged to console for debugging
- Errors clear automatically on success

#### 4. **Error State Management**
```typescript
const { error, loading, preferences } = useObsPreferences()

// In components:
<div v-if="error" class="error-message">
  {{ error }}
  <button @click="fetchPreferences()">Retry</button>
</div>
```

## Benefits

### During Development
- ✅ See detailed errors in console
- ✅ Automatic retry helps with Docker restarts
- ✅ Clear distinction between error types

### In Production
- ✅ Users see friendly, actionable messages
- ✅ Transient failures self-heal with retries
- ✅ Auth errors prompt re-login
- ✅ Validation errors show what's wrong
- ✅ Technical details hidden but logged

### For Testing
- ✅ Can simulate errors by modifying backend
- ✅ Can test retry logic by causing temporary failures
- ✅ Can verify user messages are appropriate
- ✅ Error handling is always "useful" because it's production-grade

## Example Error Scenarios

### Scenario 1: User Token Expired
```
User Action: Visits /obs-sources after 1 hour
Backend: Returns 401
Frontend: Shows "Your session has expired. Please log in again."
Action: No retry (user needs to log in)
```

### Scenario 2: Server Temporary Glitch
```
User Action: Clicks "Save Preferences"
Backend: Returns 500 (database timeout)
Frontend: Automatically retries after 1s
Backend: Success on retry
Result: User sees success, never knew there was an issue
```

### Scenario 3: Invalid Color Value
```
User Action: Enters "invalid" as chroma key color
Backend: Returns 422 with validation error
Frontend: Shows "Invalid preference values. Please check your input."
Action: No retry (user needs to fix input)
```

### Scenario 4: Docker Container Restart
```
User Action: Saves preferences during Docker restart
Backend: Network error (connection refused)
Frontend: Retries 2 times with backoff
Backend: Comes back online
Result: Success after retries
```

## Future Enhancements

Consider adding:
1. **Toast notifications** for non-blocking error display
2. **Optimistic UI updates** (update UI immediately, roll back on error)
3. **Offline detection** (detect network offline vs. server error)
4. **Error tracking** (Sentry, LogRocket) for production monitoring
5. **Circuit breaker** pattern for repeated failures

## Testing the Error Handling

### Test 401 (Expired Token)
```bash
# Manually expire token or use invalid token
localStorage.setItem('token', 'invalid-token')
# Visit /obs-sources
```

### Test 500 (Server Error)
```php
// Temporarily add to controller:
throw new \Exception('Simulated error');
```

### Test Network Error
```bash
# Stop PHP container
docker-compose stop php
# Try to load preferences
# Restart PHP container
docker-compose start php
```

### Test Validation Error
```php
// Return 422 with validation message
return new JsonResponse([
    'success' => false,
    'error' => ['message' => 'Invalid color format']
], 422);
```

## Code Pattern

This error handling pattern can be applied to other composables:
- `useAuth.ts`
- `usePlaythrough.ts`
- `useGames.ts`

Keep it consistent across the app for better UX!

