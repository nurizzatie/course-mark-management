<template>
  <div class="d-flex min-vh-100">
    <AppSidebar :navItems="navItems" @navigate="handleNavigation" />
    <div class="flex-grow-1 d-flex flex-column">
      <AppNavbar :pageTitle="pageTitle" :notifications="notifications" :showNotification="role === 'Student'" @logout="handleLogout" @update-notification="updateNotification"/>
      <div class="container-fluid p-4">
        <slot />
      </div>
    </div>
  </div>
</template>

<script>
import AppSidebar from '@/components/AppSidebar.vue'
import AppNavbar from '@/components/AppNavbar.vue'
import api from '@/api';

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
      notifications: [],
    };
  },
  methods: {
    handleNavigation() {},
    fetchNotifications() {
      const user = JSON.parse(localStorage.getItem('user'));
      console.log('Fetching notifications for user:', user); // ✅ Add this

      if (user && user.role.toLowerCase() === 'student') {
        api.get(`/students/${user.id}/notifications`)
          .then(res => {
            console.log('✅ Notifications:', res.data);
            this.notifications = res.data.notifications.map(n => ({
              ...n,
              read: !!n.seen,
              time: new Date(n.created_at).toLocaleString()
            }));
          })
          .catch(err => console.error('❌ Notification fetch failed:', err));
      } else {
        console.warn('🛑 Not a student or no user');
      }
    },
    updateNotification(index) {
      const notif = this.notifications[index];
      notif.read = true;

      api.post(`/student/notifications/${notif.id}/seen`)
        .then(() => {
          this.notifications[index].read = true;
        })
        .catch(() => console.error('Failed to mark notification as read'));
    },
    handleLogout() {
      this.$router.push('/login');
    }
  },
  mounted() {
    this.fetchNotifications();
  },

};
</script>
