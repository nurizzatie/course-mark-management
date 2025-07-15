<template>
  <div class="dropdown me-3">
    <!-- 🔔 Notification Button -->
    <button
      class="btn position-relative"
      id="notificationDropdown"
      ref="notifDropdown"
      type="button"
      @click="toggleDropdown"
    >
      <i class="fas fa-bell text-dark"></i>
      <span
        v-if="unreadCount > 0"
        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
      >
        {{ unreadCount }}
      </span>
    </button>

    <!-- 📩 Dropdown Menu -->
    <ul
      class="dropdown-menu dropdown-menu-end shadow-sm"
      ref="notifDropdownMenu"
      style="min-width: 280px; max-width: 320px; max-height: 300px; overflow-y: auto; padding: 0;"
    >
      <!-- No Notifications -->
      <li v-if="notifications.length === 0">
        <span class="dropdown-item-text text-muted px-3 py-2">No notifications</span>
      </li>

      <!-- Notification Items -->
      <li
        v-for="(notification, index) in notifications"
        :key="index"
      >
        <a
          href="#"
          class="dropdown-item d-flex align-items-start"
          :class="{ 'fw-bold': !notification.read }"
          @click.prevent="openNotification(index, notification)"
        >
          <i :class="iconClass(notification.type) + ' me-2 mt-1'"></i>
          <div class="flex-grow-1 text-wrap">
            <div>{{ notification.message }}</div>
            <small class="text-muted">{{ notification.time }}</small>
          </div>
        </a>
      </li>
    </ul>
  </div>
</template>

<script>
import * as bootstrap from 'bootstrap';

export default {
  name: 'NotificationDropdown',
  props: {
    notifications: {
      type: Array,
      default: () => [],
    },
  },
  emits: ['update-notification'],
  data() {
    return {
      dropdownInstance: null,
    };
  },
  computed: {
    unreadCount() {
      return this.notifications.filter(n => !n.read).length;
    },
  },
  mounted() {
    const el = this.$refs.notifDropdown;
    if (el) {
      this.dropdownInstance = new bootstrap.Dropdown(el);
    }
  },
  methods: {
    toggleDropdown() {
      if (this.dropdownInstance) {
        this.dropdownInstance.toggle();
      }
    },
    openNotification(index, notification) {
      if (!notification.read) {
        this.$emit('update-notification', index);
      }

      // Navigate if course_id exists
      if (notification.course_id) {
        this.$router.push(`/student/course/${notification.course_id}`);
      }
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

<style scoped>
.dropdown-menu::-webkit-scrollbar {
  width: 6px;
}

.dropdown-menu::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 5px;
}

.dropdown-menu::-webkit-scrollbar-thumb {
  background: #bbb;
  border-radius: 5px;
}

.dropdown-menu::-webkit-scrollbar-thumb:hover {
  background: #888;
}

.dropdown-item {
  white-space: normal;
  padding: 10px 15px;
}
</style>
