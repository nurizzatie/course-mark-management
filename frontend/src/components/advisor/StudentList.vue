<template>
  <div class="container py-4">
    <!-- Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h5 class="fw-bold">📋 Student Information List</h5>
    </div>

    <!-- Student Table -->
    <div class="card shadow-sm">
      <div class="card-header bg-dark text-white fw-bold">
        Assigned Students
      </div>
      <div class="card-body table-responsive">
        <table class="table table-bordered table-hover text-center align-middle">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Matric Number</th>
              <th>Remark</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!students.length">
              <td colspan="4" class="text-muted text-center py-3">No students found.</td>
            </tr>
            <tr v-for="(student, index) in students" :key="student.id" class="text-center">
              <td>{{ index + 1 }}</td>
              <td>{{ student.name }}</td>
              <td>{{ student.email }}</td>
              <td>{{ student.matric_number }}</td>
              <td>{{ student.remarks || 'No remark yet' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api';

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
        const res = await api.get('/advisor/students');
        this.students = res.data;
      } catch (err) {
        console.error('Error fetching students:', err);
      }
    }
  },
  mounted() {
    this.fetchStudents();
  }
};
</script>








