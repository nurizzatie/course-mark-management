<!-- frontend/src/layouts/AdminLayout.vue -->
<template>
  <div class="d-flex">
    <!-- Sidebar -->
    <nav class="sidebar bg-dark text-white p-3 vh-100" style="width: 220px;">
      <h4 class="text-white mb-4">Admin Panel</h4>
      <ul class="nav flex-column">
        <li class="nav-item">
          <router-link class="nav-link text-white" to="/admin/dashboard">Dashboard</router-link>
        </li>
        <li class="nav-item">
          <router-link class="nav-link text-white" to="/admin/users">Manage Users</router-link>
        </li>
        <li class="nav-item">
          <router-link class="nav-link text-white" to="/admin/assign-lecturers">Assign Lecturers</router-link>
        </li>
        <li class="nav-item">
          <router-link class="nav-link text-white" to="/admin/logs">System Logs</router-link>
        </li>
        <li class="nav-item">
          <router-link class="nav-link text-white" to="/admin/reset">Reset Password</router-link>
        </li>
      </ul>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow-1 p-4">
      <router-view />
    </main>
  </div>
</template>

<script>
export default {
  name: 'AdminLayout'
}
</script>

<style scoped>
.nav-link.router-link-exact-active {
  background-color: #0d6efd;
  font-weight: bold;
}
</style>
