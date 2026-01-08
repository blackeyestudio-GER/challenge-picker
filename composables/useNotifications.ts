import { ref } from 'vue'

export type NotificationType = 'success' | 'error' | 'info' | 'warning'

export interface Notification {
  id: string
  type: NotificationType
  message: string
  duration?: number
}

const notifications = ref<Notification[]>([])

export const useNotifications = () => {
  const addNotification = (
    type: NotificationType,
    message: string,
    duration: number = 5000
  ) => {
    const id = Date.now().toString() + Math.random().toString(36).substr(2, 9)
    const notification: Notification = {
      id,
      type,
      message,
      duration
    }

    notifications.value.push(notification)

    // Auto-remove after duration
    if (duration > 0) {
      setTimeout(() => {
        removeNotification(id)
      }, duration)
    }

    return id
  }

  const removeNotification = (id: string) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }

  const success = (message: string, duration?: number) => {
    return addNotification('success', message, duration)
  }

  const error = (message: string, duration?: number) => {
    return addNotification('error', message, duration)
  }

  const info = (message: string, duration?: number) => {
    return addNotification('info', message, duration)
  }

  const warning = (message: string, duration?: number) => {
    return addNotification('warning', message, duration)
  }

  return {
    notifications,
    success,
    error,
    info,
    warning,
    removeNotification
  }
}

