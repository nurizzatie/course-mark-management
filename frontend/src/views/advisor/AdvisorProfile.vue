<template>
  <AppLayout :role="'Advisor'" :navItems="navItems" :pageTitle="'My Profile'">
    <div class="p-6 max-w-3xl mx-auto">

          <!-- Profile Component -->
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
import api from '@/api';

export default {
  name: 'AdvisorProfile',
  components: { AppLayout },
  data() {
    return {
      form: { name: '', email: '' },
      navItems: [
        { name: 'Dashboard', link: '/advisor/dashboard' },
        { name: 'Student List', link: '/advisor/students' },
        { name: 'Review Marks', link: '/advisor/reviews' },
        { name: 'Performance Analytics', link: '/advisor/analytics' },
        { name: 'High-Risk Students', link: '/advisor/high-risk-students' },
        { name: 'Advisor Notes', link: '/advisor/notes' },
        { name: 'Profile', link: '/advisor/profile' }
      ]
    };
  },
  mounted() {
    const advisor = JSON.parse(localStorage.getItem('user'));
    if (advisor?.id) {
      api.get(`/advisor/profile/${advisor.id}`)
        .then(res => {
          this.form = res.data;
        })
        .catch(err => console.error('Failed to fetch profile:', err));
    }
  },
};
</script>


