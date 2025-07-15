<template>
  <AppLayout :role="'Advisor'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <!-- Statistic Cards -->
      <div class="row mb-4">
        <div class="col-md-3">
          <StatCard
            title="Assigned Students"
            :value="assignedStudents.toString()"
            subtitle="Current semester"
            bgClass="bg-success"
          />
        </div>
        <div class="col-md-3">
          <StatCard
            title="Consultation"
            :value="ConsultGiven.toString()"
            subtitle="This month session"
            bgClass="bg-primary"
          />
        </div>
        <div class="col-md-3">
          <StatCard
            title="Total Reviews"
            :value="TotalReviews.toString()"
            subtitle="Mark reviewed"
            bgClass="bg-warning"
          />
        </div>
        <div class="col-md-3">
          <StatCard
            title="Analytics Reports"
            :value="analyticsReports.toString()"
            subtitle="Updated weekly"
            bgClass="bg-danger"
          />
        </div>
      </div>

      <!-- High Risk Summary -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white fw-bold">
          High-Risk Student Summary
        </div>
        <div class="card-body table-responsive">
          <table class="table table-bordered text-center">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Matric No</th>
                <th>Course</th>
                <th>Overall %</th>
                <th>Percentile</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(s, i) in highRiskStudents"
                :key="s.student_id + '-' + s.course_id"
              >
                <td>{{ i + 1 }}</td>
                <td>{{ s.student_name }}</td>
                <td>{{ s.matric_number }}</td>
                <td>{{ s.course_name }}</td>
                <td>{{ s.overall_percentage }}%</td>
                <td>{{ s.percentile }}</td>
                <td>
                  <span class="badge bg-danger">At Risk</span>
                </td>
              </tr>
              <tr v-if="!highRiskStudents.length">
                <td colspan="7" class="text-muted text-center py-3">
                  🎉 No high-risk students currently.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from '@/components/StatCard.vue';
import api from '@/api';

export default {
  name: 'AdvisorDashboard',
  components: { AppLayout, StatCard },
  data() {
    return {
      pageTitle: 'Dashboard',
      assignedStudents: 0,
      ConsultGiven: 0,
      TotalReviews: 0,
      analyticsReports: 0,
      highRiskStudents: [],
      navItems: [
        { name: 'Dashboard', link: '/advisor/dashboard' },
        { name: 'Advisees', link: '/advisor/students' },
        { name: 'Mark Review', link: '/advisor/reviews' },
        { name: 'Performance Analytics', link: '/advisor/analytics' },
        { name: 'Consultation', link: '/advisor/notes' },
        { name: 'Profile', link: '/advisor/profile' }
      ]
    };
  },
  mounted() {
    this.loadDashboardStats();
    this.fetchAnalytics();
  },
  methods: {
    async loadDashboardStats() {
      const user = JSON.parse(localStorage.getItem('user'));
      if (!user?.id) return;

      try {
        const res = await api.get(`/advisor/${user.id}/dashboard-stats`);
        const stats = res.data;

        this.assignedStudents = stats.assigned_students ?? 0;
        this.ConsultGiven = stats.feedback_given ?? 0;
        this.TotalReviews = stats.pending_reviews ?? 0;
        this.analyticsReports = stats.analytics_reports ?? 0;
      } catch (err) {
        console.error('Error loading dashboard stats:', err);
      }
    },

    async fetchAnalytics() {
      const user = JSON.parse(localStorage.getItem('user'));
      if (!user?.id) return;

      try {
        const res = await api.get('/advisor/analytics', {
          headers: {
            'X-User': JSON.stringify(user)
          }
        });

        const analytics = res.data || [];

        // Filter high-risk students only
        this.highRiskStudents = analytics.filter(
      (item) => item.risk_level?.toLowerCase() === 'high'
    );
      } catch (err) {
        console.error('Failed to load analytics data:', err);
        this.highRiskStudents = [];
      }
    }
  }
};
</script>







