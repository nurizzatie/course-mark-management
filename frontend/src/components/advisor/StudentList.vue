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
        <!-- Search Bar -->
        <div class="mb-3 input-group">
          <span class="input-group-text bg-light">
            <i class="fas fa-search"></i>
          </span>
          <input v-model="searchQuery" class="form-control" placeholder="Search by name or matric no" />
        </div>
        <table class="table table-bordered table-hover text-center align-middle">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Matric Number</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!students.length">
              <td colspan="4" class="text-muted text-center py-3">No students found.</td>
            </tr>
            <tr v-for="(student, index) in filteredStudents" :key="student.id">
              <td>{{ index + 1 }}</td>
              <td>{{ student.name }}</td>
              <td>{{ student.email }}</td>
              <td>{{ student.matric_number }}</td>
              <td>
                <div>
                  <router-link :to="`/advisor/advisee/${student.id}/progress`" class="btn btn-sm btn-dark">View</router-link>
                </div>
              </td>
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
      user: JSON.parse(localStorage.getItem('user')),
      students: [],
      searchQuery: '',
    };
  },
  computed: {
    filteredStudents() {
      const q = this.searchQuery.toLowerCase();
      return this.students.filter(
        s =>
          s.name.toLowerCase().includes(q) ||
          s.matric_number.toLowerCase().includes(q)
      );
    }
  },
  methods: {
    async fetchStudents() {
      try {
        const res = await api.get('/advisor/students', {
          headers: {
            'X-User': JSON.stringify(this.user)
          }
        });
        this.students = res.data;
      } catch (err) {
        console.error('Error fetching students:', err);
        alert('Failed to load assigned students.');
      }
    }
  },
  mounted() {
    this.fetchStudents();
  }
};
</script>