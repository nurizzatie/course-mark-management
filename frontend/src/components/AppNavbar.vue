<template>
  <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm px-4 py-2">
    <div class="container-fluid">
      <!-- Mobile Sidebar Toggle (☰) -->
      <button
        class="btn d-md-none me-2"
        type="button"
        data-bs-toggle="offcanvas"
        data-bs-target="#mobileSidebar"
        aria-controls="mobileSidebar"
      >
        ☰
      </button>

      <!-- Page Title -->
      <span class="navbar-brand fw-bold">{{ pageTitle }}</span>

      <!-- Right Side -->
      <div class="d-flex align-items-center ms-auto">
        
        <!-- Notification -->
        <NotificationDropdown
          v-if="showNotification"
          :notifications="notifications"
          @update-notification="updateNotification"
      />

        <span class="badge bg-light text-dark fs-6">{{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}</span>

        <!-- User Dropdown -->
        <div class="dropdown">
        <button
          class="btn dropdown-toggle"
          type="button"
          ref="userDropdown"
          @click="toggleUserDropdown"
        >
          {{ user.name }}
        </button>
          <ul class="dropdown-menu dropdown-menu-end" ref="userDropdownMenu">
            <li>
              <a class="dropdown-item d-flex align-items-center" href="#" @click.prevent="goToProfile">
                <i class="fas fa-user me-2 text-secondary"></i>
                Profile
              </a>
            </li>
            <li><hr class="dropdown-divider" /></li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="#" @click.prevent="logout">
                <i class="fas fa-sign-out-alt me-2 text-secondary"></i>
                Logout
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
import NotificationDropdown from '@/components/NotificationDropdown.vue';
import * as bootstrap from 'bootstrap';

export default {
  name: 'AppNavbar',
  components: {
    NotificationDropdown,
  },
  data() {
    return {
      dropdownInstance: null
    };
  },
  props: {
    pageTitle: {
      type: String,
      required: true,
    },
    notifications: {
      type: Array,
      default: () => [],
    },
    showNotification: {
      type: Boolean,
      default: false,
    },  
  },
  mounted() {
    const dropdownEl = this.$refs.userDropdown;
    if (dropdownEl) {
      this.dropdownInstance = new bootstrap.Dropdown(dropdownEl);
    }
  },
  computed: {
    user() {
      return JSON.parse(localStorage.getItem('user')) || {};
    },
    unreadCount() {
      return this.notifications.filter(n => !n.read).length;
    },
  },
  methods: {
    goToProfile() {
  const role = this.user.role?.toLowerCase();
  if (role === 'advisor') {
    this.$router.push('/advisor/profile');
  } else if (role === 'lecturer') {
    this.$router.push('/lecturer/profile');
  } else if (role === 'admin') {
    this.$router.push('/admin/profile');
  } else if (role === 'student') {
    this.$router.push('/student/profile');
  } else {
    this.$router.push('/login');
  }
},

    logout() {
      this.$emit('logout')
    },
    updateNotification(index) {
      this.$emit('update-notification', index);
    },
    toggleUserDropdown() {
      if (this.dropdownInstance) {
        this.dropdownInstance.toggle();
      }
    }
  },
}
</script>

<style scoped>
.navbar {
  z-index: 1030;
}

.badge {
  font-size: 0.6rem;
}
</style>
