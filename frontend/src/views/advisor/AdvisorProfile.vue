<template>
  <AppLayout :role="'Advisor'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <!-- Profile Info -->
      <div class="card mb-4" v-if="profile">
        <div class="card-header bg-dark text-white">
          <i class="fa-solid fa-address-card"></i> Profile Information
        </div>
        <div class="card-body">
          <p><strong>Name:</strong> {{ profile.name }}</p>
          <p><strong>Email:</strong> {{ profile.email }}</p>
          <p><strong>Matric No:</strong> {{ profile.matric_number }}</p>
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
          this.students = res.data.students;
        })
        .catch(err => {
          console.error('Failed to load profile info:', err);
        });
    }
  }
};
</script>





