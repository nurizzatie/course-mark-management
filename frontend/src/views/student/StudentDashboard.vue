<template>
  <AppLayout :role="'Student'" :navItems="navItems" :pageTitle="'Dashboard'">
    <div class="container mt-4">
      <!-- Welcome Message -->
      <div class="mb-4">
        <h2>Hello, {{ studentName }}</h2>
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

     <!-- Percentile Carousel & Grade Predictor Row -->
<div class="row">
  <!-- Course Percentile Carousel -->
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header fw-bold">📈 Your Course Percentiles</div>
      <div class="card-body">
        <div class="scrollbar-visible">
        <Carousel :items-to-show="1.5" :wrap-around="true">
          <Slide v-for="(course, index) in courses" :key="index">
            <div class="text-center p-3 border rounded w-75 mx-auto">
              <h6>{{ course.name }}</h6>
              <svg width="150" height="150">
                <circle
                  cx="75"
                  cy="75"
                  r="65"
                  stroke="#eee"
                  stroke-width="14"
                  fill="none"
                />
                <circle
                  cx="75"
                  cy="75"
                  r="65"
                  :stroke="course.percentile === 0 ? '#ccc' : '#0d6efd'"
                  stroke-width="14"
                  fill="none"
                  :stroke-dasharray="2 * Math.PI * 65"
                  :stroke-dashoffset="2 * Math.PI * 65 * (1 - (course.percentile || 0) / 100)"
                  transform="rotate(-90 75 75)"
                />
                <text
                  x="50%"
                  y="52%"
                  text-anchor="middle"
                  font-size="20"
                  fill="#000"
                  dy=".3em"
                >
                  {{ course.percentile > 0 ? course.percentile + '%' : 'No Data' }}
                </text>
              </svg>
            </div>
          </Slide>
        </Carousel>
        </div>
      </div>
    </div>
  </div>

  <!-- Grade Predictor -->
  <div class="col-md-6 mb-4">
    <div class="card h-100 d-flex flex-column justify-content-between">
      <div class="card-body">
        <h5 class="card-title">🎯 Grade Predictor</h5>
        <p class="card-text">Simulate your grade based on future marks.</p>
      </div>
      <div class="card-footer text-end bg-transparent border-top-0">
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
import { Carousel, Slide } from 'vue3-carousel'
import 'vue3-carousel/dist/carousel.css'

export default {
  name: "StudentDashboard",
  components: {
    AppLayout,
    Carousel,
    Slide
  },
  data() {
    return {
      studentName: '',
      studentMatricNumber: '',
      studentSemester: '',
      studentRank: 0,
      studentPercentile: 0,
      totalStudents: 1,
      summaryCards: [],
      courses: [],
      radius: 50,
      navItems: [
        { name: 'Dashboard', link: '/student/dashboard', active: true },
        { name: 'My Courses', link: '/student/courses', active: false },
        { name: 'Performance Tools', link: '/student/performance', active: false },
        { name: 'Notifications', link: '/student/notifications', active: false },
        { name: 'Profile', link: '/student/profile', active: false }
      ]
    };
  },
  computed: {
    circumference() {
      return 2 * Math.PI * this.radius;
    },
    dashOffset() {
      return this.circumference * (1 - this.studentPercentile / 100);
    }
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

        this.studentName = data.student.name;
        this.studentMatricNumber = data.student.matric_number;
        this.studentSemester = data.courses.length > 0 ? data.courses[0].semester : 'N/A';
        this.studentRank = data.student.rank;
        this.studentPercentile = data.student.percentile;
        this.totalStudents = data.student.total_students;

        this.courses = data.courses.map(course => ({
          id: course.id,
          name: course.course_name,
          code: course.course_code,
          progress: course.progress,
          semester: course.semester,
          percentile: course.percentile ?? 0
        }));

        this.summaryCards = data.summaryCards;

      } catch (error) {
        console.error("❌ Failed to load dashboard:", error);
        alert("Unable to load student dashboard data.");
      }
    }
  }
};
</script>

<style scoped>
.circle-bg {
  stroke: #eee;
}
.circle-fg {
  stroke: #0d6efd;
  transition: stroke-dashoffset 0.6s ease;
}
.circle-progress-wrapper {
  display: inline-block;
  position: relative;
}
.circle-bg,
.circle-fg {
  transform: rotate(-90deg);
  transform-origin: 50% 50%;
}

/* Show scrollbar even when not scrolling (for Chrome/Webkit) */
.scrollbar-visible {
  overflow-x: scroll;
  white-space: nowrap;
  scrollbar-width: thin; /* for Firefox */
}

.scrollbar-visible::-webkit-scrollbar {
  height: 8px;
}

.scrollbar-visible::-webkit-scrollbar-thumb {
  background-color: #aaa;
  border-radius: 4px;
}

</style>
