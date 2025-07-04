<template>
  <AppLayout :role="'Admin'" :navItems="navItems" :pageTitle="pageTitle">
    <h2 class="mb-4 fw-bold">Assign Lecturers</h2>
    <!-- TODO: Add form for assigning lecturers -->
    <p>This page will contain a form to assign lecturers to courses.</p>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';

export default {
  name: 'AssignLecturers',
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
      pageTitle: 'Assign Lecturers',
    }
  }
}
</script>
