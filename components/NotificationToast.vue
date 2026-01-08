<template>
  <Teleport to="body">
    <div class="notification-container">
      <TransitionGroup
        name="notification"
        tag="div"
        class="notification-list"
      >
        <div
          v-for="notification in notifications"
          :key="notification.id"
          :class="[
            'notification-toast',
            `notification-toast--${notification.type}`
          ]"
        >
          <div class="notification-toast__icon">
            <svg
              v-if="notification.type === 'success'"
              class="w-5 h-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 13l4 4L19 7"
              />
            </svg>
            <svg
              v-else-if="notification.type === 'error'"
              class="w-5 h-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
            <svg
              v-else-if="notification.type === 'warning'"
              class="w-5 h-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
              />
            </svg>
            <svg
              v-else
              class="w-5 h-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <div class="notification-toast__content">
            <p class="notification-toast__message">{{ notification.message }}</p>
          </div>
          <button
            @click="removeNotification(notification.id)"
            class="notification-toast__close"
            aria-label="Close notification"
          >
            <svg
              class="w-4 h-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { useNotifications } from '~/composables/useNotifications'

const { notifications, removeNotification } = useNotifications()
</script>

<style scoped>
.notification-container {
  @apply fixed top-4 right-4 z-50 pointer-events-none;
}

.notification-list {
  @apply flex flex-col gap-2;
}

.notification-toast {
  @apply flex items-start gap-3 p-4 rounded-lg shadow-lg backdrop-blur-md border pointer-events-auto min-w-[320px] max-w-md;
  animation: slideInRight 0.3s ease-out;
}

.notification-toast--success {
  @apply bg-green-900/90 border-green-700/50 text-green-100;
}

.notification-toast--error {
  @apply bg-red-900/90 border-red-700/50 text-red-100;
}

.notification-toast--warning {
  @apply bg-yellow-900/90 border-yellow-700/50 text-yellow-100;
}

.notification-toast--info {
  @apply bg-blue-900/90 border-blue-700/50 text-blue-100;
}

.notification-toast__icon {
  @apply flex-shrink-0 mt-0.5;
}

.notification-toast__content {
  @apply flex-1 min-w-0;
}

.notification-toast__message {
  @apply text-sm font-medium;
}

.notification-toast__close {
  @apply flex-shrink-0 p-1 rounded hover:bg-white/10 transition-colors;
  @apply text-current opacity-70 hover:opacity-100;
}

/* Transition animations */
.notification-enter-active,
.notification-leave-active {
  @apply transition-all duration-300 ease-out;
}

.notification-enter-from {
  @apply opacity-0 translate-x-full;
}

.notification-leave-to {
  @apply opacity-0 translate-x-full;
}

.notification-move {
  @apply transition-transform duration-300;
}

/* Slide in animation */
@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(100%);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}
</style>

