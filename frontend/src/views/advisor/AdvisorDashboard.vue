<template>
  <AppLayout :role="'Advisor'" :navItems="navItems" :pageTitle="pageTitle">
    <h2 class="mb-4 fw-bold">Welcome, Advisor</h2>
    <div class="row g-3">
      <div class="col-md-6 col-lg-3">
        <StatCard title="Assigned Students" value="30" subtitle="Current semester" bgClass="bg-success" />
      </div>
      <div class="col-md-6 col-lg-3">
        <StatCard title="Feedback Given" value="18" subtitle="This month" bgClass="bg-primary" />
      </div>
      <div class="col-md-6 col-lg-3">
        <StatCard title="Pending Reviews" value="5" subtitle="Awaiting action" bgClass="bg-warning" />
      </div>
      <div class="col-md-6 col-lg-3">
        <StatCard title="Analytics Reports" value="3" subtitle="Updated weekly" bgClass="bg-danger" />
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from '@/components/StatCard.vue';

export default {
  name: 'AdvisorDashboard',
  components: { AppLayout, StatCard },
  data() {
    return {
      navItems: [
        { name: 'Dashboard', link: '/advisor/dashboard' },
        { name: 'Student List', link: '/advisor/students' },
        { name: 'Review Marks', link: '/advisor/reviews' },
        { name: 'Performance Analytics', link: '/advisor/analytics' },
        { name: 'Profile', link: '/advisor/profile' }
      ],
      pageTitle: 'Dashboard',
    }
  }
}
</script>


