<template>
  <AppLayout :role="'Admin'" :navItems="navItems" :pageTitle="pageTitle">
    <h2 class="mb-4 fw-bold">Welcome, Admin</h2>
    <!-- <div class="row g-3">
      <div class="col-md-6 col-lg-3">
        <StatCard title="Total Students" value="120" subtitle="Current semester" bgClass="bg-success" />
      </div>
      <div class="col-md-6 col-lg-3">
        <StatCard title="Courses Taught" value="4" subtitle="Active courses" bgClass="bg-primary" />
      </div>
      <div class="col-md-6 col-lg-3">
        <StatCard title="Pending Marks" value="8" subtitle="Assignments not submitted" bgClass="bg-warning" />
      </div>
      <div class="col-md-6 col-lg-3">
        <StatCard title="Recent Feedbacks" value="12" subtitle="Last 7 days" bgClass="bg-danger" />
      </div>
    </div> -->
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
// import StatCard from '@/components/StatCard.vue';

export default {
  name: 'AdminDashboard',
  components: { 
    AppLayout, 
    // StatCard 
  },
  data() {
    return {
      navItems: [
        { name: 'Dashboard', link: '/admin/dashboard' },
        //add other nav-link name and routes
      ],
      pageTitle: 'Dashboard',
    }
  }
}
</script>
