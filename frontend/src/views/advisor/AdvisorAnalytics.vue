<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold mb-4">Student Performance Analytics</h2>

    <table class="w-full table-auto border">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2">Student</th>
          <th class="px-4 py-2">Course</th>
          <th class="px-4 py-2">Total Mark</th>
          <th class="px-4 py-2">Final Exam</th>
          <th class="px-4 py-2">Overall %</th>
          <th class="px-4 py-2">Rank</th>
          <th class="px-4 py-2">Percentile</th>
          <th class="px-4 py-2">Risk</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in analytics" :key="item.id" class="border-t">
          <td class="px-4 py-2">{{ item.student_name }}</td>
          <td class="px-4 py-2">{{ item.course_name }}</td>
          <td class="px-4 py-2">{{ item.total_mark }}</td>
          <td class="px-4 py-2">{{ item.final_exam_mark }}</td>
          <td class="px-4 py-2">{{ item.overall_percentage }}%</td>
          <td class="px-4 py-2">{{ item.rank }}</td>
          <td class="px-4 py-2">{{ item.percentile }}%</td>
          <td class="px-4 py-2">{{ item.risk_level }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AdvisorAnalytics',
  data() {
    return {
      analytics: []
    };
  },
  async mounted() {
    try {
      const res = await axios.get('http://localhost:8080/api/advisor/analytics');
      this.analytics = res.data;
    } catch (err) {
      console.error('Failed to fetch analytics data:', err);
    }
  }
};
</script>

<style scoped>
table {
  border-collapse: collapse;
}
th, td {
  text-align: left;
}
</style>
