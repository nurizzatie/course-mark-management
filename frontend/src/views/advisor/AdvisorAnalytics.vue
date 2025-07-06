<template>
  <AppLayout :role="'Advisor'" :navItems="navItems" :pageTitle="'Performance Analytics'">
    <div class="p-6 max-w-6xl mx-auto">
      <h2 class="text-2xl font-bold mb-4 text-indigo-700">📈 Student Performance Analytics</h2>

      <div v-if="analytics.length">
        <table class="w-full table-auto border border-gray-300 rounded-md">
          <thead class="bg-indigo-100">
            <tr>
              <th class="px-4 py-2 text-left">Student</th>
              <th class="px-4 py-2 text-left">Course</th>
              <th class="px-4 py-2 text-left">Total Mark</th>
              <th class="px-4 py-2 text-left">Final Exam</th>
              <th class="px-4 py-2 text-left">Overall %</th>
              <th class="px-4 py-2 text-left">Rank</th>
              <th class="px-4 py-2 text-left">Percentile</th>
              <th class="px-4 py-2 text-left">Risk</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in analytics" :key="item.student_id + '-' + item.course_id" class="border-t">
              <td class="px-4 py-2">{{ item.student_name }}</td>
              <td class="px-4 py-2">{{ item.course_name }}</td>
              <td class="px-4 py-2">{{ item.total_mark }}</td>
              <td class="px-4 py-2">{{ item.final_exam_mark }}</td>
              <td class="px-4 py-2">{{ item.overall_percentage }}%</td>
              <td class="px-4 py-2">{{ item.rank }}</td>
              <td class="px-4 py-2">{{ item.percentile }}%</td>
              <td class="px-4 py-2 font-semibold" :class="getRiskClass(item.risk_level)">
                {{ item.risk_level }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <p v-else class="text-gray-500">No analytics data available yet.</p>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';

export default {
  name: 'AdvisorAnalytics',
  components: { AppLayout },
  data() {
    return {
      analytics: [],
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
  methods: {
    async fetchAnalytics() {
      try {
        const res = await axios.get('http://localhost:8080/api/advisor/analytics');
        this.analytics = res.data;
      } catch (err) {
        console.error('Failed to fetch analytics data:', err);
        this.analytics = [];
      }
    },
    getRiskClass(risk) {
      if (risk === 'High') return 'text-red-600';
      if (risk === 'Medium') return 'text-yellow-600';
      if (risk === 'Low') return 'text-green-600';
      return '';
    }
  },
  mounted() {
    this.fetchAnalytics();
  }
};
</script>

<style scoped>
table {
  border-collapse: collapse;
}
</style>

