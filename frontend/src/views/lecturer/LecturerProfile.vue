<template>
  <AppLayout :role="'Lecturer'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <div class="card mb-4">
        <div class="card-header bg-dark text-white"><i class="fa-solid fa-address-card"></i> Profile Information</div>
        <div class="card-body">
          <p><strong>Name:</strong> {{ profile.name }}</p>
          <p><strong>Email:</strong> {{ profile.email }}</p>
          <p><strong>Matric No:</strong> {{ profile.matric_number }}</p>
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
            </tbody>
          </table>
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
        this.courses = res.data.courses;
      }).catch(() => {
        alert('Failed to load profile data');
      });
    }
  }
};
</script>
