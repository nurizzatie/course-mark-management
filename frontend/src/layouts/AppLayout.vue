<template>
  <div class="d-flex min-vh-100">
    <AppSidebar :navItems="navItems" @navigate="handleNavigation" />
    <div class="flex-grow-1 d-flex flex-column">
      <AppNavbar :pageTitle="pageTitle" :notifications="notifications" :showNotification="role === 'Lecturer' || role === 'Student'" @logout="handleLogout" @update-notification="updateNotification"/>
      <div class="container-fluid p-4">
        <slot />
      </div>
    </div>
  </div>
</template>

<script>
import AppSidebar from '@/components/AppSidebar.vue'
import AppNavbar from '@/components/AppNavbar.vue'

export default {
  name: 'AppLayout',
  components: { AppSidebar, AppNavbar },
  props: {
    navItems: {
      type: Array,
      required: true,
    },
    pageTitle: {
      type: String,
      required: true,
    },
    role: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      notifications: [
        {
          message: 'Assignment submitted',
          read: false,
          type: 'success',
          time: '2 mins ago',
        },
        {
          message: 'New student registered',
          read: false,
          type: 'info',
          time: '10 mins ago',
        },
        {
          message: 'Meeting at 3PM',
          read: true,
          type: 'warning',
          time: '1 hour ago',
        },
      ],
    };
  },
  methods: {
    handleNavigation() {},
    handleLogout() {
      this.$router.push('/login');
    },
    updateNotification(index) {
      this.notifications[index].read = true;
    },
  },
};
</script>
