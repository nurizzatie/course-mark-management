<template>
  <AppLayout :role="'Lecturer'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <div class="card mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
          <span><i class="fa-solid fa-address-card"></i> Profile Information</span>
          <div v-if="!isEditing">
            <button class="btn btn-sm btn-light" @click="isEditing = true">
              <i class="fas fa-edit"></i> Edit
            </button>
          </div>
        </div>
        <div class="card-body">
          <div v-if="isEditing">
            <div class="mb-2">
              <label class="form-label">Name</label>
              <input v-model="editProfile.name" class="form-control" />
            </div>
            <div class="mb-2">
              <label class="form-label">Email</label>
              <input v-model="editProfile.email" class="form-control" />
            </div>
            <div class="mb-2">
              <label class="form-label">Matric No</label>
              <input v-model="editProfile.matric_number" class="form-control" disabled/>
            </div>
            <div class="d-flex justify-content-end">
              <button class="btn btn-secondary me-2" @click="isEditing = false">Cancel</button>
              <button class="btn btn-dark" @click="updateProfile">Save</button>
            </div>
          </div>
          <div v-else>
            <p><strong>Name:</strong> {{ profile.name }}</p>
            <p><strong>Email:</strong> {{ profile.email }}</p>
            <p><strong>Matric No:</strong> {{ profile.matric_number }}</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header bg-dark text-white"><i class="fa-solid fa-book"></i> Courses Taught</div>
        <div class="card-body table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="table-dark">
              <tr class="text-center">
                <th>#</th>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Semester</th>
                <th>Year</th>
              </tr>
            </thead>
            <tbody>
              <tr class="text-center" v-for="(course, index) in courses" :key="index">
                <td>{{ index + 1 }}</td>
                <td>{{ course.course_code }}</td>
                <td>{{ course.course_name }}</td>
                <td>{{ course.semester }}</td>
                <td>{{ course.year }}</td>
              </tr>

              <tr v-if="courses.length === 0">
                <td colspan="5" class="text-center text-muted">
                  No students in this course.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Toast Message -->
      <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999" v-if="showToast">
        <div class="toast show text-white" :class="toastClass">
          <div class="d-flex">
            <div class="toast-body">{{ toastMessage }}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="showToast = false"></button>
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
  name: 'LecturerProfile',
  components: { AppLayout },
  data() {
    return {
      pageTitle: 'My Profile',
      navItems: [
        { name: 'Dashboard', link: '/lecturer/dashboard' },
        { name: 'My Courses', link: '/lecturer/courses' },
        { name: 'Student Enrollment', link: '/lecturer/students' },
        { name: 'Remark Requests', link: '/lecturer/remark-requests' },
        { name: 'Profile', link: '/lecturer/profile' },
      ],
      profile: {},
      courses: [],
      isEditing: false,
      editProfile: {},
      showToast: false,
      toastMessage: '',
      toastClass: 'bg-success'
    };
  },
  mounted() {
    this.fetchProfile();
  },
  methods: {
    fetchProfile() {
      api.get('/lecturer/profile', {
        headers: {
          'X-User': JSON.stringify(JSON.parse(localStorage.getItem('user')))
        }
      }).then(res => {
        this.profile = res.data.profile;
        this.editProfile = { ...res.data.profile };
        this.courses = res.data.courses;
      }).catch(() => {
        alert('Failed to load profile data');
      });
    },
    updateProfile() {
      api.put('/lecturer/profile', this.editProfile, {
        headers: {
          'Content-Type': 'application/json',
          'X-User': JSON.stringify(JSON.parse(localStorage.getItem('user')))
        }
      }).then(() => {
        this.profile = { ...this.editProfile };
        this.isEditing = false;
        this.showToastMessage('Profile updated successfully!', 'bg-success');
      }).catch(() => {
        this.showToastMessage('Failed to update profile.', 'bg-danger');
      });
    },
    showToastMessage(message, cssClass = 'bg-success') {
      this.toastMessage = message;
      this.toastClass = cssClass;
      this.showToast = true;
      setTimeout(() => this.showToast = false, 3000);
    },
    cancelEdit() {
      this.isEditing = false;
      this.editProfile = { ...this.profile };
    }
  }
};
</script>
