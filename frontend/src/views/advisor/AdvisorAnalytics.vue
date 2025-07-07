<template>
  <AppLayout :role="'Advisor'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <!-- Page Title + Search + Download -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="w-100 me-3">
          <h5 class="fw-bold mb-2">📊 Student Performance Analytics</h5>
          <input
            type="text"
            class="form-control"
            placeholder="🔍 Search by Matric No or Student Name..."
            v-model="searchQuery"
          />
        </div>
        <button class="btn btn-primary mt-4" @click="downloadAnalytics">
          ⬇️ Download Report
        </button>
      </div>

      <!-- Analytics Table -->
      <div class="card shadow-sm">
        <div class="card-header bg-dark text-white fw-bold">
          Performance Summary
        </div>
        <div class="card-body table-responsive">
          <table class="table table-bordered table-hover text-center align-middle">
            <thead class="table-dark">
              <tr>
                <th>Student</th>
                <th>Matric No</th>
                <th>Course</th>
                <th>Total Mark</th>
                <th>Final Exam</th>
                <th>Overall %</th>
                <th>Rank</th>
                <th>Percentile</th>
                <th>Risk</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!filteredAnalytics.length">
                <td colspan="9" class="text-muted text-center py-3">No matching results.</td>
              </tr>
              <tr
                v-for="item in filteredAnalytics"
                :key="item.student_id + '-' + item.course_id"
              >
                <td>{{ item.student_name }}</td>
                <td>{{ item.matric_number }}</td>
                <td>{{ item.course_name }}</td>
                <td>{{ item.total_mark }}</td>
                <td>{{ item.final_exam_mark }}</td>
                <td>{{ item.overall_percentage }}%</td>
                <td>{{ item.rank }}</td>
                <td>{{ item.percentile }}%</td>
                <td :class="['fw-bold', getRiskClass(item.risk_level)]">
                  {{ item.risk_level }}
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
import api from '@/api';

export default {
  name: 'AdvisorAnalytics',
  components: { AppLayout },
  data() {
    return {
      pageTitle: 'Performance Analytics',
      analytics: [],
      searchQuery: '',
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
  computed: {
    filteredAnalytics() {
      const q = this.searchQuery.toLowerCase().trim();
      return this.analytics.filter(item =>
        item.matric_number.toLowerCase().includes(q) ||
        item.student_name.toLowerCase().includes(q)
      );
    }
  },
  methods: {
    async fetchAnalytics() {
      try {
        const res = await api.get('/advisor/analytics');
        this.analytics = res.data;
      } catch (err) {
        console.error('Failed to fetch analytics data:', err);
        this.analytics = [];
      }
    },
    getRiskClass(risk) {
      switch (risk) {
        case 'High': return 'text-danger';
        case 'Medium': return 'text-warning';
        case 'Low': return 'text-success';
        default: return 'text-secondary';
      }
    },
    downloadAnalytics() {
      if (!this.filteredAnalytics.length) {
        return alert("No data to download.");
      }

      const header = [
        'Student Name',
        'Matric Number',
        'Course',
        'Total Mark',
        'Final Exam',
        'Overall %',
        'Rank',
        'Percentile',
        'Risk Level'
      ];

      const rows = this.filteredAnalytics.map(item => [
        item.student_name,
        item.matric_number,
        item.course_name,
        item.total_mark,
        item.final_exam_mark,
        item.overall_percentage,
        item.rank,
        item.percentile,
        item.risk_level
      ]);

      const csvContent = [header.join(','), ...rows.map(r => r.join(','))].join('\n');
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.setAttribute('download', 'performance_analytics.csv');
      link.click();
    }
  },
  mounted() {
    this.fetchAnalytics();
  }
};
</script>







