<template>
  <div class="p-6 max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-4">Student List</h2>

    <table class="w-full border border-gray-300 rounded-md">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2 text-left">Name</th>
          <th class="px-4 py-2 text-left">Email</th>
          <th class="px-4 py-2 text-left">Matric Number</th>
          <th class="px-4 py-2 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="student in students" :key="student.id" class="border-t">
          <td class="px-4 py-2">{{ student.name }}</td>
          <td class="px-4 py-2">{{ student.email }}</td>
          <td class="px-4 py-2">{{ student.matric_number }}</td>
          <td class="px-4 py-2 text-center">
            <button
              @click="submitFeedback(student)"
              class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700"
            >
              Submit Feedback
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'StudentList',
  data() {
    return {
      students: []
    };
  },
  methods: {
    async fetchStudents() {
      try {
        const response = await axios.get('http://localhost/backend/public/api/advisor/students');
        this.students = response.data;
      } catch (err) {
        console.error('Error fetching students:', err);
      }
    },
    submitFeedback(student) {
      alert(`Open feedback form for ${student.name} (${student.matric_number})`);
      // Later, this will open AdvisorNoteForm.vue or a modal
    }
  },
  mounted() {
    this.fetchStudents();
  }
};
</script>

<style scoped>
table {
  border-collapse: collapse;
}
</style>


