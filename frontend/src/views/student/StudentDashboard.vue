<template>
  <AppLayout :role="'Student'" :navItems="navItems" :pageTitle="'Dashboard'">
    <div class="container mt-4">
      <!-- Welcome Message -->
      <div class="mb-4">
        <h2>Welcome back, {{ studentName }} 👋</h2>
        <p class="text-muted">
          Matric Number: {{ studentMatricNumber }} | {{ studentSemester }}
        </p>
      </div>

      <!-- Summary Cards -->
      <div class="row mb-4">
        <div class="col-md-3 mb-3" v-for="(card, index) in summaryCards" :key="index">
          <div class="card text-white bg-primary h-100">
            <div class="card-body">
              <h5 class="card-title">{{ card.icon }} {{ card.title }}</h5>
              <p class="card-text fs-4">{{ card.value }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- My Courses Table -->
      <div class="card mb-4">
        <div class="card-header fw-bold">My Courses</div>
        <div class="card-body p-0">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Course Name</th>
                <th>Code</th>
                <th>Progress</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="course in courses" :key="course.id">
                <td>{{ course.name }}</td>
                <td>{{ course.code }}</td>
                <td>
                  <div class="progress">
                    <div
                      class="progress-bar"
                      role="progressbar"
                      :style="{ width: course.progress + '%' }"
                      :aria-valuenow="course.progress"
                      aria-valuemin="0"
                      aria-valuemax="100"
                    >
                      {{ course.progress }}%
                    </div>
                  </div>
                </td>
                <td>
                  <router-link :to="`/student/course/${course.id}`" class="btn btn-outline-primary btn-sm">
                    View Details
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Performance & Predictor Section -->
      <div class="row">
        <!-- Class Rank -->
        <div class="col-md-6 mb-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Class Rank</h5>
              <p class="card-text">
                You're currently in the <strong class="text-success">{{ studentRank }}</strong> percentile.
              </p>
            </div>
          </div>
        </div>

        <!-- Grade Predictor -->
        <div class="col-md-6 mb-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">🎯 Grade Predictor</h5>
              <p class="card-text">Simulate your grade based on future marks.</p>
              <router-link to="/student/predictor" class="btn btn-primary">
                Open Predictor
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from "@/layouts/AppLayout.vue";

export default {
  name: "StudentDashboard",
  components: { AppLayout },
  data() {
    return {
      studentName: '',
      studentMatricNumber: '',
      studentSemester: '',
      studentRank: 'Top 15%',
      summaryCards: [],
      courses: [],
      navItems: [
        { name: 'Dashboard', link: '/student/dashboard', active: true },
        { name: 'My Courses', link: '/student/courses', active: false },
        { name: 'Performance Tools', link: '/student/performance', active: false },
        { name: 'Notifications', link: '/student/notifications', active: false },
        { name: 'Profile', link: '/student/profile', active: false }
      ]
    };
  },
  mounted() {
    this.loadStudentData();
  },
  methods: {
    async loadStudentData() {
      const user = JSON.parse(localStorage.getItem('user'));
      if (!user?.id) {
        alert("Please login first.");
        return;
      }

      try {
        const res = await fetch(`http://localhost:8080/api/student/${user.id}/dashboard`);
        const data = await res.json();

        // Student info
        this.studentName = data.student.name;
        this.studentMatricNumber = data.student.matric_number;
        this.studentSemester = data.courses.length > 0 ? data.courses[0].semester : 'N/A';
        this.studentRank = data.student.rank;

        // Courses
        this.courses = data.courses.map(course => ({
          id: course.id,
          name: course.course_name,
          code: course.course_code,
          progress: course.progress,
          semester: course.semester
        }));

        // Summary Cards
        this.summaryCards = data.summaryCards;

        console.log("✅ Dashboard loaded:", data);
      } catch (error) {
        console.error("❌ Failed to load dashboard:", error);
        alert("Unable to load student dashboard data.");
      }
    }
  }
};
</script>

