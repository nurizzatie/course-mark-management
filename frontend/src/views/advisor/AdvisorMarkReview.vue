<template>
  <AppLayout :role="'Advisor'" :navItems="navItems" :pageTitle="'Mark Review'">
    <div class="p-6 max-w-5xl mx-auto">
      <ReviewMark />
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
import ReviewMark from '@/components/advisor/ReviewMark.vue';

export default {
  name: 'AdvisorMarkReview',
  components: {
    AppLayout,
    ReviewMark
  },
  data() {
    return {
      navItems: [
        { name: 'Dashboard', link: '/advisor/dashboard' },
        { name: 'Advisees', link: '/advisor/students' },
        { name: 'Mark Review', link: '/advisor/reviews' },
        { name: 'Performance Analytics', link: '/advisor/analytics' },
        { name: 'Consultation', link: '/advisor/notes' },
        { name: 'Profile', link: '/advisor/profile' }
      ]
    };
  }
};
</script>

