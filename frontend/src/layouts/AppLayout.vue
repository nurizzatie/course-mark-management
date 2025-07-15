<template>
  <div class="d-flex min-vh-100">
    <AppSidebar :navItems="navItems" @navigate="handleNavigation" />

    <div class="flex-grow-1 d-flex flex-column">
      <!-- ✅ Main Navbar -->
      <AppNavbar
        :pageTitle="pageTitle"
        :notifications="notifications"
        :showNotification="role === 'Student' || role === 'Admin'"
        @logout="handleLogout"
        @update-notification="updateNotification"
      />

      <!-- ✅ Main Content Slot -->
      <div class="container-fluid p-4">
        <slot />
      </div>
    </div>
  </div>
</template>

<script>
import AppSidebar from "@/components/AppSidebar.vue";
import AppNavbar from "@/components/AppNavbar.vue";
import api from "@/api";

export default {
  name: "AppLayout",
  components: {
    AppSidebar,
    AppNavbar,
  },
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
      const user = JSON.parse(localStorage.getItem("user"));
      if (user) {
        const role = user.role.toLowerCase();
        const endpoint =
          role === "admin"
            ? `/admin/notifications/${user.id}`
            : `/students/${user.id}/notifications`;

        api
          .get(endpoint)
          .then((res) => {
            this.notifications = res.data.notifications.map((n) => ({
              ...n,
              read: !!n.seen,
              time: new Date(n.created_at).toLocaleString(),
            }));
          })
          .catch((err) => console.error("Notification fetch failed:", err));
      }
    },
    updateNotification(index) {
      const notif = this.notifications[index];
      notif.read = true;

      api
        .post(`/student/notifications/${notif.id}/seen`)
        .then(() => {
          this.notifications[index].read = true;
        })
        .catch(() => console.error("Failed to mark notification as read"));
    },
    handleLogout() {
      this.$router.push("/login");
    },
  },
  mounted() {
    this.fetchNotifications();
  },
};
</script>
