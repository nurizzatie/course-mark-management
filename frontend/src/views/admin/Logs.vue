<template>
  <AppLayout :role="'Admin'" :navItems="navItems" :pageTitle="pageTitle">
    <h2 class="mb-4 fw-bold">System Logs</h2>
    <!-- TODO: Display logs -->
    <p>This page will display recent system activity logs.</p>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';

export default {
  name: 'SystemLogs',
  components: { AppLayout },
  data() {
    return {
      navItems: [
        { name: 'Dashboard', link: '/admin/dashboard' },
        { name: 'Manage Users', link: '/admin/users' },
        { name: 'Assign Lecturers', link: '/admin/assign' },
        { name: 'Logs', link: '/admin/logs' },
        { name: 'Reset Password', link: '/admin/reset' },
      ],
      pageTitle: 'Logs',
    }
  }
}
</script>
