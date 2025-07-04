<template>
  <div class="p-6 max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-4">Student Marks Review</h2>

    <table class="w-full border border-gray-300 rounded-md">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2 text-left">Student Name</th>
          <th class="px-4 py-2 text-left">Matric Number</th>
          <th class="px-4 py-2 text-left">Assessment</th>
          <th class="px-4 py-2 text-left">Obtained Mark</th>
          <th class="px-4 py-2 text-left">Last Updated</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="record in marks" :key="`${record.student_id}-${record.assessment_name}`" class="border-t">
          <td class="px-4 py-2">{{ record.student_name }}</td>
          <td class="px-4 py-2">{{ record.matric_number }}</td>
          <td class="px-4 py-2">{{ record.assessment_name }}</td>
          <td class="px-4 py-2">{{ record.obtained_mark }}</td>
          <td class="px-4 py-2">{{ formatDate(record.updated_at) }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AdvisorMarkReview',
  data() {
    return {
      marks: []
    };
  },
  methods: {
    async fetchMarks() {
      try {
        const response = await axios.get('http://localhost:8080/api/advisor/marks');
        this.marks = response.data;
      } catch (err) {
        console.error('Error fetching marks:', err);
      }
    },
    formatDate(datetime) {
      return new Date(datetime).toLocaleString();
    }
  },
  mounted() {
    this.fetchMarks();
  }
};
</script>

<style scoped>
table {
  border-collapse: collapse;
}
</style>
