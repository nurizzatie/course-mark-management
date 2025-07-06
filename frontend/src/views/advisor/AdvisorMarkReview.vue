<template>
  <AppLayout :role="'Advisor'" :navItems="navItems" :pageTitle="'Review Student Marks'">
    <div class="p-6 max-w-5xl mx-auto">
      <h2 class="text-2xl font-bold mb-4 text-blue-700">📊 Student Assessment Marks</h2>
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
        { name: 'Student List', link: '/advisor/students' },
        { name: 'Review Marks', link: '/advisor/reviews' },
        { name: 'Performance Analytics', link: '/advisor/analytics' },
        { name: 'High-Risk Students', link: '/advisor/high-risk-students' },
        { name: 'Advisor Notes', link: '/advisor/notes' },
        { name: 'Profile', link: '/advisor/profile' }
      ]
    };
  }
};
</script>

