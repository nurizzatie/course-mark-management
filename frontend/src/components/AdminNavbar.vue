<template>
  <nav class="admin-navbar">
    <div class="left">
      <h2>Admin Dashboard</h2>
    </div>
    <div class="right">
      <span class="username">{{ username }}</span>
      <button @click="toggleNotifications" class="notif-btn">
        🔔 <span v-if="notifications.length">({{ notifications.length }})</span>
      </button>

      <!-- 🔔 Notification Dropdown -->
      <div v-if="showNotifs" class="notif-dropdown">
        

        <ul v-if="notifications.length">
          <li v-for="notif in notifications" :key="notif.id">
            {{ notif.email }} requested password reset at {{ formatDate(notif.created_at) }}
          </li>
        </ul>
        <p v-else style="color: gray; padding: 10px;">No reset requests.</p>
      </div>
    </div>
  </nav>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AdminNavbar',
  data() {
    return {
      username: 'Admin',
      notifications: [],
      showNotifs: false,
    };
  },
  mounted() {
    this.fetchNotifications();
  },
  methods: {
    toggleNotifications() {
      this.showNotifs = !this.showNotifs;
    },

    async fetchNotifications() {
      try {
        const response = await axios.get('http://localhost:8080/api/reset-requests');
        this.notifications = response.data || [];
        console.log("🔔 Notifications fetched:", JSON.parse(JSON.stringify(this.notifications)));
      } catch (error) {
        console.error('Failed to fetch notifications', error);
      }
    },

    formatDate(datetime) {
      const d = new Date(datetime);
      return d.toLocaleString(); // e.g., "7/8/2025, 12:28:58 AM"
    }
  }
};
</script>

<style scoped>
.admin-navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #2c3e50;
  color: white;
  padding: 10px 20px;
  position: relative;
}

.right {
  display: flex;
  align-items: center;
  gap: 10px;
  position: relative; /* ✅ Needed for dropdown */
}

.notif-btn {
  background: none;
  border: none;
  font-size: 18px;
  color: white;
  cursor: pointer;
  position: relative;
}

.notif-dropdown {
  position: absolute;
  top: 40px; /* ✅ Adjust if needed */
  right: 0;
  background: white;
  color: black;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  min-width: 300px;
  z-index: 9999;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.notif-dropdown ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.notif-dropdown li {
  padding: 8px 10px;
  border-bottom: 1px solid #eee;
  font-size: 14px;
}

.notif-dropdown li:last-child {
  border-bottom: none;
}
</style>
