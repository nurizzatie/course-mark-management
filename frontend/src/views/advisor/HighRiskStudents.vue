<template>
  <div class="p-6 max-w-6xl mx-auto">
    <h2 class="text-2xl font-bold mb-4 text-red-700">⚠️ High Risk Students</h2>

    <table class="w-full border border-gray-300 rounded-md">
      <thead class="bg-red-100">
        <tr>
          <th class="px-4 py-2">Name</th>
          <th class="px-4 py-2">Matric No</th>
          <th class="px-4 py-2">Email</th>
          <th class="px-4 py-2">Course</th>
          <th class="px-4 py-2">Overall %</th>
          <th class="px-4 py-2">Percentile</th>
          <th class="px-4 py-2">Risk Level</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="student in students" :key="student.student_id" class="border-t">
          <td class="px-4 py-2">{{ student.student_name }}</td>
          <td class="px-4 py-2">{{ student.matric_number }}</td>
          <td class="px-4 py-2">{{ student.email }}</td>
          <td class="px-4 py-2">{{ student.course_code }}</td>
          <td class="px-4 py-2">{{ student.overall_percentage }}%</td>
          <td class="px-4 py-2">{{ student.percentile }}%</td>
          <td class="px-4 py-2 font-semibold text-red-600">{{ student.risk_level }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import api from '@/api';

export default {
  name: 'HighRiskStudents',
  data() {
    return {
      students: []
    };
  },
  methods: {
    async fetchHighRiskStudents() {
      try {
        const res = await api.get('/advisor/high-risk-students');
        this.students = res.data;
      } catch (err) {
        console.error('Failed to load high risk students:', err);
      }
    }
  },
  mounted() {
    this.fetchHighRiskStudents();
  }
};
</script>

<style scoped>
th {
  text-align: left;
}
</style>
