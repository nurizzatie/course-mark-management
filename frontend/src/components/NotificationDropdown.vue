<template>
  <div class="dropdown me-3">
    <button
      class="btn position-relative"
      id="notificationDropdown"
      data-bs-toggle="dropdown"
      aria-expanded="false"
    >
      <i class="fas fa-bell text-dark"></i>
      <span
        v-if="unreadCount > 0"
        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
      >
        {{ unreadCount }}
      </span>
    </button>

    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="min-width: 250px;">
      <li v-if="notifications.length === 0">
        <span class="dropdown-item-text text-muted">No notifications</span>
      </li>
      <li
        v-for="(notification, index) in notifications"
        :key="index"
      >
        <a
          href="#"
          class="dropdown-item d-flex align-items-start"
          :class="{ 'fw-bold': !notification.read }"
          @click.prevent="openNotification(index)"
        >
          <i :class="iconClass(notification.type) + ' me-2 mt-1'"></i>
          <div class="flex-grow-1">
            <div>{{ notification.message }}</div>
            <small class="text-muted">{{ notification.time }}</small>
          </div>
        </a>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'NotificationDropdown',
  props: {
    notifications: {
      type: Array,
      default: () => [],
    },
  },
  computed: {
    unreadCount() {
      return this.notifications.filter(n => !n.read).length;
    },
  },
  methods: {
    openNotification(index) {
      const notification = this.notifications[index];
      if (!notification.read) {
        this.$emit('update-notification', index);
      }
      alert(`Opening: ${notification.message}`);
    },
    iconClass(type) {
      switch (type) {
        case 'info':
          return 'fas fa-info-circle text-primary';
        case 'success':
          return 'fas fa-check-circle text-success';
        case 'warning':
          return 'fas fa-exclamation-circle text-warning';
        case 'error':
          return 'fas fa-times-circle text-danger';
        default:
          return 'fas fa-bell text-secondary';
      }
    },
  },
};
</script>
