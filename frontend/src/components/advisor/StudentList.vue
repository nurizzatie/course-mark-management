<template>
  <div class="container py-4">
    <!-- Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h5 class="fw-bold">📋 Student Information List</h5>
    </div>

    <!-- Add Student Section -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-primary text-white fw-bold">
        ➕ Add New Student
      </div>
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <div class="col-md-6">
            <label class="form-label">Enter Matric Number</label>
            <input
              type="text"
              v-model="newStudentMatric"
              class="form-control"
              placeholder="e.g. A123456"
            />
          </div>
          <div class="col-md-3">
            <button
              class="btn btn-dark w-100"
              @click="addStudent"
              :disabled="adding"
            >
              {{ adding ? 'Adding...' : 'Add Student' }}
            </button>
          </div>
        </div>
        <p v-if="addError" class="text-danger mt-2">{{ addError }}</p>
        <p v-if="addSuccess" class="text-success mt-2">{{ addSuccess }}</p>
      </div>
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
            <tr v-if="!filteredStudents.length">
              <td colspan="5" class="text-muted text-center py-3">No students found.</td>
            </tr>
            <tr v-for="(student, index) in filteredStudents" :key="student.id">
              <td>{{ index + 1 }}</td>
              <td>{{ student.name }}</td>
              <td>{{ student.email }}</td>
              <td>{{ student.matric_number }}</td>
              <td>
                <router-link :to="`/advisor/advisee/${student.id}/progress`" class="btn btn-sm btn-dark">View</router-link>
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
      newStudentMatric: '',
      adding: false,
      addError: '',
      addSuccess: ''
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
    },
    async addStudent() {
  this.addError = '';
  this.addSuccess = '';
  const matric = this.newStudentMatric.trim();

  if (!matric) {
    this.addError = 'Please enter a matric number.';
    return;
  }

  this.adding = true;

  try {
    await api.post('/advisor/students', {
      matric_number: matric
    }, {
      headers: {
        'X-User': JSON.stringify(this.user)
      }
    });

    this.addSuccess = 'Student added successfully.';
    this.newStudentMatric = '';
    await this.fetchStudents();

  } catch (err) {
    console.error('Add student error:', err);
    this.addError = err?.response?.data?.error || 'Failed to add student.';
  } finally {
    this.adding = false;
  }
}

  },
  mounted() {
    this.fetchStudents();
  }
};
</script>
