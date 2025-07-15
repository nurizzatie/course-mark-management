<template>
  <div class="container py-4">
    <h5 class="fw-bold mb-4">📋 Student Information List</h5>

    <!-- Add Student -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-primary text-white fw-bold">➕ Add New Student</div>
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <div class="col-md-6">
            <label class="form-label">Enter Matric Number</label>
            <input v-model.trim="newStudentMatric" type="text" class="form-control" placeholder="e.g. A123456" />
          </div>
          <div class="col-md-3">
            <button class="btn btn-dark w-100" @click="addStudent" :disabled="adding">
              {{ adding ? 'Adding…' : 'Add Student' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Assigned Students -->
    <div class="card shadow-sm">
      <div class="card-header bg-dark text-white fw-bold">Assigned Students</div>
      <div class="card-body table-responsive">
        <!-- Search -->
        <div class="mb-3 input-group">
          <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
          <input v-model.trim="searchQuery" class="form-control" placeholder="Search by name or matric no" />
        </div>

        <table class="table table-bordered table-hover text-center align-middle">
          <thead class="table-dark">
            <tr>
              <th>#</th><th>Name</th><th>Email</th><th>Matric No.</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!filteredStudents.length">
              <td colspan="5" class="text-muted py-3">No students found.</td>
            </tr>
            <tr v-for="(student, index) in filteredStudents" :key="student.id">
              <td>{{ index + 1 }}</td>
              <td>{{ student.name }}</td>
              <td>{{ student.email }}</td>
              <td>{{ student.matric_number }}</td>
              <td class="d-flex gap-2 justify-content-center">
                <router-link :to="`/advisor/advisee/${student.id}/progress`" class="btn btn-sm btn-dark">View</router-link>
                <button class="btn btn-sm btn-danger" @click="removeStudent(student.id)" :disabled="removingId === student.id">
                  {{ removingId === student.id ? 'Removing…' : 'Remove' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" v-if="toast.visible" style="z-index: 9999;">
      <div class="toast text-white show" :class="`bg-${toast.type}`">
        <div class="d-flex">
          <div class="toast-body">{{ toast.message }}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="toast.visible = false"></button>
        </div>
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
      user: JSON.parse(localStorage.getItem('user') || '{}'),
      students: [],
      searchQuery: '',
      newStudentMatric: '',
      adding: false,
      removingId: null,
      toast: {
        visible: false,
        message: '',
        type: 'success', // 'success' | 'danger' | 'warning'
      }
    };
  },
  computed: {
    filteredStudents() {
      const q = this.searchQuery.toLowerCase();
      return this.students.filter(
        s => s.name.toLowerCase().includes(q) || s.matric_number.toLowerCase().includes(q)
      );
    }
  },
  methods: {
    async fetchStudents() {
      try {
        const { data } = await api.get('/advisor/students', {
          headers: { 'X-User': JSON.stringify(this.user) }
        });
        this.students = data;
      } catch (err) {
        console.error('Error loading students:', err);
        this.showToast('Failed to load students', 'danger');
      }
    },
    showToast(message, type = 'success') {
      this.toast.message = message;
      this.toast.type = type;
      this.toast.visible = true;
      setTimeout(() => (this.toast.visible = false), 3000);
    },
    async addStudent() {
      const matric = this.newStudentMatric.trim();
      if (!matric) {
        this.showToast('Please enter a matric number.', 'warning');
        return;
      }

      this.adding = true;
      try {
        const response = await api.post('/advisor/students', {
          matric_number: matric
        }, {
          headers: { 'X-User': JSON.stringify(this.user) }
        });

        if (response.status === 200 || response.status === 201) {
          this.newStudentMatric = '';
          await this.fetchStudents();
          this.showToast('Student added successfully');
        } else {
          this.showToast('Unexpected response from server', 'warning');
        }
      } catch (err) {
        const status = err?.response?.status;
        if (status === 409) {
          this.showToast('Student is already assigned.', 'warning');
        } else {
          console.error('Add student error:', err);
          this.showToast(err?.response?.data?.error || 'Failed to add student', 'danger');
        }
      } finally {
        this.adding = false;
      }
    },
    async removeStudent(studentId) {
      if (!confirm('Remove this student from your list?')) return;

      this.removingId = studentId;
      try {
        await api.delete(`/advisor/students/${studentId}`, {
          headers: { 'X-User': JSON.stringify(this.user) }
        });
        this.students = this.students.filter(s => s.id !== studentId);
        this.showToast('Student removed.', 'warning');
      } catch (err) {
        console.error('Remove student error:', err);
        this.showToast('Failed to remove student', 'danger');
      } finally {
        this.removingId = null;
      }
    }
  },
  mounted() {
    this.fetchStudents();
  }
};
</script>




