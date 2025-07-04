<template>
  <AppLayout :role="'Advisor'" :navItems="navItems" :pageTitle="pageTitle">
    <h2 class="mb-4 fw-bold">Welcome, Advisor</h2>
    <div class="row g-3">
      <div class="col-md-6 col-lg-3">
  <StatCard title="At-Risk Students" :value="stats.atRiskCount" subtitle="High risk level" bgClass="bg-info" />
      </div>
      <div class="col-md-6 col-lg-3">
        <StatCard title="Total Students" :value="stats.totalStudents" subtitle="Current semester" bgClass="bg-success" />
      </div>
      <div class="col-md-6 col-lg-3">
        <StatCard title="Courses Assigned" :value="stats.coursesTaught" subtitle="Active courses" bgClass="bg-primary" />
      </div>
      <div class="col-md-6 col-lg-3">
        <StatCard title="Pending Reviews" :value="stats.pendingMarks" subtitle="Awaiting advisor review" bgClass="bg-warning" />
      </div>
      <div class="col-md-6 col-lg-3">
        <StatCard title="Recent Feedbacks" :value="stats.recentFeedbacks" subtitle="Last 7 days" bgClass="bg-danger" />
      </div>
    </div>

  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from '@/components/StatCard.vue';
import axios from 'axios';

export default {
  name: 'AdvisorDashboard',
  components: { AppLayout, StatCard },
  data() {
    return {
      stats: {
        totalStudents: 0,
        coursesTaught: 0,
        pendingMarks: 0,
        recentFeedbacks: 0,
        atRiskCount: 0,
      },
      navItems: [
        { name: 'Dashboard', link: '/advisor/dashboard' },
        { name: 'Student List', link: '/advisor/students' },
        { name: 'Review Marks', link: '/advisor/reviews' },
        { name: 'Performance Analytics', link: '/advisor/analytics' },
        { name: 'Profile', link: '/advisor/profile' },
      ],
      pageTitle: 'Dashboard',
    }
  },
  mounted() {
    // Fetch main dashboard stats
  axios.get('http://localhost:8080/advisor/1/dashboard')
    .then(res => {
      this.stats = { ...this.stats, ...res.data };
    })
    .catch(err => {
      console.error('Failed to load advisor dashboard stats:', err);
    });

    // Example: replace '1' with actual logged-in advisor ID
    axios.get('http://localhost:8080/advisor/1/dashboard')
      .then(res => {
        this.stats = res.data;
      })
      .catch(err => {
        console.error('Failed to load advisor dashboard stats:', err);
      });
  }
}
</script>

