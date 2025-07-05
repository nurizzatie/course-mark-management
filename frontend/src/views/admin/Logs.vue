<template>
  <AppLayout :role="'Admin'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <h2 class="mb-4 fw-bold">System Logs</h2>
      <!-- TODO: Display logs -->
      <p>This page will display recent system activity logs.</p>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue'
import adminNavItems from '@/constants/adminNavItems'

export default {
  name: 'SystemLogs',
  components: { AppLayout },
  data() {
    return {
      navItems: adminNavItems,
      pageTitle: 'Logs'
    }
  }
}
</script>
