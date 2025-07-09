<template>
  <AppLayout :role="'Advisor'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <!-- Profile Info -->
      <div class="card mb-4" v-if="profile">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
          <span><i class="fa-solid fa-address-card"></i> Profile Information</span>
          <div v-if="!editing">
            <button class="btn btn-sm btn-light" @click="editing = true">
              <i class="fas fa-edit"></i> Edit
            </button>
          </div>
        </div>
        <div class="card-body">
          <div v-if="editing">
            <div class="mb-2">
              <label class="form-label">Name</label>
              <input v-model="form.name" class="form-control" />
            </div>
            <div class="mb-2">
              <label class="form-label">Email</label>
              <input v-model="form.email" class="form-control" />
            </div>
            <div class="mb-2">
              <label class="form-label">Matric No</label>
              <input v-model="form.matric_number" class="form-control" disabled/>
            </div>
            <div class="d-flex justify-content-end">
              <button class="btn btn-secondary me-2" @click="editing = false">Cancel</button>
              <button class="btn btn-dark" @click="saveProfile">Save</button>
            </div>
          </div>
          <div v-else>
            <p><strong>Name:</strong> {{ profile.name }}</p>
            <p><strong>Email:</strong> {{ profile.email }}</p>
            <p><strong>Matric No:</strong> {{ profile.matric_number }}</p>
          </div>
        </div>
      </div>

      <!-- Students Assigned -->
      <div class="card" v-if="students && students.length">
        <div class="card-header bg-dark text-white">
          <i class="fa-solid fa-people-group"></i> Students Assigned
        </div>
        <div class="card-body table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="table-dark">
              <tr class="text-center">
                <th>#</th>
                <th>Name</th>
                <th>Matric No</th>
                <th>Email</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(student, index) in students" :key="student.id" class="text-center">
                <td>{{ index + 1 }}</td>
                <td>{{ student.name }}</td>
                <td>{{ student.matric_number }}</td>
                <td>{{ student.email }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <p v-else-if="students" class="text-muted">No students assigned yet.</p>
      <p v-else class="text-muted">Loading students...</p>

      <!-- Toast Notification -->
      <div class="toast-container position-fixed bottom-0 end-0 p-3" v-if="showingToast" style="z-index: 9999">
        <div class="toast show text-white" :class="toastType">
          <div class="d-flex">
            <div class="toast-body">{{ toastMessage }}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="showingToast = false"></button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
import api from '@/api';

export default {
  name: 'AdvisorProfile',
  components: { AppLayout },
  data() {
    return {
      pageTitle: 'My Profile',
      profile: null, // Start with null to detect loading state
      students: [],
      editing: false,
      form: {
        name: '',
        email: '',
        matric_number: '',
      },
      showingToast: false,
      toastMessage: '',
      toastType: 'bg-success',
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
  mounted() {
    const advisor = JSON.parse(localStorage.getItem('user'));

    if (advisor?.id) {
      api.get(`/advisor/profile/${advisor.id}`)
        .then(res => {
          this.profile = res.data.profile;
          this.form = {
            name: this.profile.name,
            email: this.profile.email,
            matric_number: this.profile.matric_number
          };
          this.students = res.data.students;
        })
        .catch(err => {
          console.error('Failed to load profile info:', err);
        });
    }
  },
  methods: {
    saveProfile() {
      api.put(`/advisor/profile/${this.profile.id}`, this.form)
        .then(() => {
          this.profile = { ...this.form };
          this.editing = false;
          this.showToast('Profile updated successfully', 'bg-success');
        })
        .catch(() => {
          this.showToast('Failed to update profile', 'bg-danger');
        });
    },
    showToast(message, type = 'bg-success') {
      this.toastMessage = message;
      this.toastType = type;
      this.showingToast = true;
      setTimeout(() => {
        this.showingToast = false;
      }, 3000);
    },
    cancelEdit() {
      this.isEditing = false;
      this.editProfile = { ...this.profile };
    }
  }
};
</script>





