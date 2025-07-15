

<template>
  <AppLayout :role="'Student'" :navItems="navItems" :pageTitle="'Dashboard'">
    <div class="studentDashboard">

      <!-- Welcome Message -->
      <div class="mb-4">
        <h2>Hello, {{ studentName }}</h2>
        <p class="text-muted">
          Matric Number: {{ studentMatricNumber }} | Semester:
          {{ studentSemester }}
        </p>
      </div>

      <div>
        <StudyLineChart />
      </div>

      <br />

      <div class="summary">
        <!-- My Courses Table -->
        <div class="card mb-4">
          <div class="card-header bg-dark text-white text-center">
            My Courses
          </div>
          <div class="card-body p-0">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Course Name</th>
                  <th>Code</th>
                  <th class="text-center">Progress</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <!-- If there are courses, loop and display -->
                <template v-if="courses.length">
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
                    <td class="text-center">
                      <router-link
                        :to="`/student/course/${course.id}`"
                        class="btn btn-dark"
                      >
                        View Details
                      </router-link>
                    </td>
                  </tr>
                </template>

                <!-- If no courses -->
                <tr v-else>
                  <td colspan="4" class="text-center text-muted py-4">
                    No courses found.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Two-Column Row -->
        <div class="row">
          <!-- Course Percentile Chart -->
          <div class="col-md-6 mb-4">
            <div class="card h-100">
              <div class="card-header bg-dark text-white text-center">
                 Your Course Percentiles
              </div>
              <div class="card-body">
                <BarChart :chartData="barChartData" :chartTitle="'Your Course Percentiles'" />
              </div>
            </div>
          </div>

          <!-- Calendar -->
          <div class="col-md-6 mb-4">
            <div class="card h-100">
              <div class="card-header bg-dark text-white text-center">
                Calendar
              </div>
              <div
                class="card-body d-flex justify-content-center align-items-center"
              >
                <DatePicker v-model="date" expanded />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from "@/layouts/AppLayout.vue";
import { DatePicker } from "v-calendar";
import "vue3-carousel/dist/carousel.css";
import "v-calendar/style.css";
import StudyLineChart from "@/components/StudyLineChart.vue";
import BarChart from "@/components/BarChart.vue";
import api from '@/api';

export default {
  name: "StudentDashboard",
  components: {
    AppLayout,
    BarChart,
    StudyLineChart,
    DatePicker
  },

  data() {
    return {
      studentName: "",
      studentMatricNumber: "",
      studentSemester: "",
      studentRank: 0,
      studentPercentile: 0,
      totalStudents: 1,
      summaryCards: [],
      courses: [],
      radius: 50,
      date: new Date(),
      navItems: 
      [
        { name: "Dashboard", link: "/student/dashboard", active: true },
        { name: "Performance Tools",link: "/student/performance", active: false },
      ],
    };
  },
  computed: {
    circumference() {
      return 2 * Math.PI * this.radius;
    },
    dashOffset() {
      return this.circumference * (1 - this.studentPercentile / 100);
    },
    barChartData() {
      return {
        labels: this.courses.map((c) => c.name),
        datasets: [
          {
            label: "",
            data: this.courses.map((c) => c.percentile || 0),
            backgroundColor: [
              "rgba(255, 99, 132, 0.2)",
              "rgba(54, 162, 235, 0.2)",
              "rgba(255, 206, 86, 0.2)",
              "rgba(75, 192, 192, 0.2)",
              "rgba(153, 102, 255, 0.2)",
              "rgba(255, 159, 64, 0.2)",

            ],
            borderColor: [
              "rgba(255,99,132,1)",
              "rgba(54, 162, 235, 1)",
              "rgba(255, 206, 86, 1)",
              "rgba(75, 192, 192, 1)",
              "rgba(153, 102, 255, 1)",
              "rgba(255, 159, 64, 1)",
            ],
            borderWidth: 1,
          },
        ],
      };
    },
  },
  mounted() {
    this.loadStudentData();
  },
  methods: {
    async loadStudentData() {
      const user = JSON.parse(localStorage.getItem("user"));
      if (!user?.id) {
        alert("Please login first.");
        return;
      }

      try {
        const res = await api.get(`/student/${user.id}/dashboard`);
        const data = await res.data;

        this.studentName = data.student.name;
        this.studentMatricNumber = data.student.matric_number;
        this.studentSemester = data.courses.length > 0 ? data.courses[0].semester : "N/A";
        this.studentRank = data.student.rank;
        this.studentPercentile = data.student.percentile;
        this.totalStudents = data.student.total_students;

        this.courses = data.courses.map((course) => ({
          id: course.id,
          name: course.course_name,
          code: course.course_code,
          progress: course.progress,
          semester: course.semester,
          percentile: course.percentile ?? 0,
        }));

      } catch (error) {
        console.error("Failed to load dashboard:", error);
        alert("Unable to load student dashboard data.");
      }
    },
  },
};
</script>


<style scoped>

.studentDashboard {
  max-width: 100%;
  margin: auto;
  padding: 2rem;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style>
