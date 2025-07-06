<template>
  <AppLayout :role="'Advisor'" :navItems="navItems" :pageTitle="'Student List'">
    <div class="p-6 max-w-5xl mx-auto">

      <!-- Student List Component -->
      <StudentList />
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
import StudentList from '@/components/advisor/StudentList.vue';

export default {
  name: 'AdvisorStudentList',
  components: {
    AppLayout,
    StudentList
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







