<template>
  <AppLayout :role="'Student'" :navItems="navItems" :pageTitle="pageTitle">
    <h2 class="mb-4 fw-bold">Welcome, {{ studentName || 'Student' }}</h2>

    <div v-if="courses.length">
      <div class="row g-3">
        <div class="col-md-6 col-lg-4" v-for="course in courses" :key="course.id">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">{{ course.code }} - {{ course.name }}</h5>
              <p class="card-text">Semester: {{ course.semester }}</p>
              <router-link :to="`/student/courses/${course.id}`" class="btn btn-primary btn-sm">
                View Details
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else>
      <p class="text-muted">You are not enrolled in any courses yet.</p>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';

export default {
  name: 'StudentDashboard',
  components: {
    AppLayout
  },
  data() {
    return {
      studentName: '', // Can be populated from localStorage or API
      courses: [],     // Will be populated via API
      navItems: [
        { name: 'Dashboard', link: '/student/dashboard', active: true }, //student default subject
        { name: 'My Courses', link: '/student/courses', active: false }, // student learning courses
        { name: 'Performance Tools', link: '/student/performance', active: false },   // GPA/CGPA calculator, What-If analysis
  { name: 'Notifications', link: '/student/notifications', active: false }, // Announcements or course-related alerts
        { name: 'Profile', link: '/student/profile', active: false }
      ],
      pageTitle: 'Student Dashboard'
    };
  },
  mounted() {
    this.loadStudentData();
  },
  methods: {
    loadStudentData() {
      const user = JSON.parse(localStorage.getItem('user'));
      this.studentName = user?.name || 'Student';

      // Simulate course data (replace with real API call later)
      this.courses = [
        { id: 1, code: 'SECP3453', name: 'Web Programming', semester: 'Semester 1' },
        { id: 2, code: 'SECJ2153', name: 'Data Structures', semester: 'Semester 1' },
        { id: 3, code: 'SECD2613', name: 'System Analysis and Design', semester: 'Semester 1' },
        // Add more if needed
      ];
    }
  }
};
</script>
