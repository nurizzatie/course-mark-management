<template>
  <AppLayout :role="'Lecturer'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">

      <!-- Statistic Cards -->
      <div class="row mb-4">
        <div class="col-md-4">
          <StatCard
            title="Total Students"
            :value="totalStudents.toString()"
            subtitle="Enrolled this semester"
            bgClass="bg-dark"
          />
        </div>
        <div class="col-md-4">
          <StatCard
            title="Total Courses"
            :value="totalCourses.toString()"
            subtitle="Courses you teach"
            bgClass="bg-dark"
          />
        </div>
        <div class="col-md-4">
          <StatCard
            title="Total Assessments"
            :value="totalAssessments.toString()"
            subtitle="Created for this course"
            bgClass="bg-dark"
          />
        </div>
      </div>

      <!-- Course Selection -->
      <div class="mb-3">
        <label class="form-label fw-bold">Select Course</label>
        <select v-model="selectedCourseId" class="form-select w-auto">
          <option disabled value="">-- Select a course --</option>
          <option v-for="course in courses" :key="course.id" :value="course.id">
            {{ course.course_code }} - {{ course.course_name }}
          </option>
        </select>
      </div>

      <!-- Charts -->
      <div class="row mb-4">
        <div class="col-md-6 mb-4">
          <h6 class="fw-bold">📊 Grade Distribution</h6>
          <div style="min-height: 300px">
            <BarCharts v-if="barChartData" :chartData="barChartData" :chartOptions="barOptions" />
          </div>
        </div>
        <div class="col-md-6 mb-4">
          <h6 class="fw-bold">📈 Average Performance Trend</h6>
          <div style="min-height: 300px">
            <LineChart v-if="lineChartData" :chartData="lineChartData" :chartOptions="lineOptions" />
          </div>
        </div>
      </div>

      <!-- Student Table -->
      <div class="card shadow-sm">
        <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
          <span>Student Performance Summary</span>
          <router-link
            :to="`/lecturer/courses/${selectedCourseId}/marks`"
            class="btn btn-sm btn-outline-light"
          >
            View Full Marks
          </router-link>
        </div>
        <div class="card-body table-responsive">
          <table class="table table-bordered text-center">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Matric No</th>
                <th>Total (%)</th>
                <th>Grade</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(s, i) in students" :key="s.id">
                <td>{{ i + 1 }}</td>
                <td>{{ s.name }}</td>
                <td>{{ s.matric_number }}</td>
                <td>{{ s.total?.toFixed(2) ?? '-' }}</td>
                <td>{{ s.grade ?? '-' }}</td>
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
import BarCharts from '@/components/BarCharts.vue';
import LineChart from '@/components/LineChart.vue';
import StatCard from '@/components/StatCard.vue';
import api from '@/api';

export default {
  name: 'LecturerDashboard',
  components: { AppLayout, BarCharts, LineChart, StatCard },
  data() {
    return {
      user: JSON.parse(localStorage.getItem('user')),
      navItems: [
        { name: 'Dashboard', link: '/lecturer/dashboard' },
        { name: 'My Courses', link: '/lecturer/courses' },
        { name: 'Student Enrollment', link: '/lecturer/students' },
        { name: 'Remark Requests', link: '/lecturer/remark-requests' },
        { name: 'Profile', link: '/lecturer/profile' },
      ],
      pageTitle: 'Dashboard',
      courses: [],
      selectedCourseId: '',
      students: [],
      assessments: [],
      barChartData: null, 
      lineChartData: null,
      totalStudents: 0,
      totalCourses: 0,
      totalAssessments: 0,
      barOptions: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
      },
      lineOptions: {
        responsive: true,
        tension: 0.3,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true } }
      }
    };
  },
  mounted() {
    this.loadCourses();
  },
  watch: {
    selectedCourseId(newId, oldId) {
      if (newId && newId !== oldId) {
        console.log('Course changed to:', newId);
        this.loadAnalytics();
      }
    }
  },
  methods: {
    loadCourses() {
      api.get('/lecturer/my-courses', {
        headers: { 'X-User': JSON.stringify(this.user) }
      }).then(res => {
        this.courses = res.data.courses;
        this.totalCourses = this.courses.length;

        if (this.courses.length > 0 && !this.selectedCourseId) {
          this.selectedCourseId = this.courses[0].id; // only assign if not yet selected
        }
      });
    },

    loadAnalytics() {
      if (!this.selectedCourseId) return;

      console.log('Fetching analytics for course', this.selectedCourseId);

      // Reset charts to ensure re-render
      this.barChartData = null;
      this.lineChartData = null;

      api.get(`/lecturer/courses/${this.selectedCourseId}/analytics`)
        .then(res => {
          this.students = res.data.students;
          this.assessments = res.data.assessments;

          this.totalStudents = this.students.length;
          this.totalAssessments = this.assessments.length;

          // Bar Chart - Grade Distribution
          const gradeCount = {};
          this.students.forEach(s => {
            gradeCount[s.grade] = (gradeCount[s.grade] || 0) + 1;
          });

          this.barChartData = {
            labels: Object.keys(gradeCount),
            datasets: [{
              label: 'Number of Students',
              data: Object.values(gradeCount),
              backgroundColor: Object.keys(gradeCount).map(g => {
                return g.startsWith('A+') ? '#800000'
                     : g.startsWith('A') ? '#A52A2A'
                     : g.startsWith('A-') ? '#A0522D'
                     : g.startsWith('B+') ? '#8B4513'
                     : g.startsWith('B') ? '#B22222'
                     : g.startsWith('B-') ? '#DC143C'
                     : g.startsWith('C+') ? '#CD5C5C'
                     : g.startsWith('C') ? '#F08080'
                     : g.startsWith('C-') ? '#E9967A'
                     : g.startsWith('D+') ? '#FA8072'
                     : g.startsWith('D') ? '#FFA07A'
                     : g.startsWith('D-') ? '#CD853F'
                     : '#FF0000';
              })
            }]
          };

          // Line Chart - Assessment Averages
          const labels = this.assessments.map(a => a.title);
          const averages = this.assessments.map(a => {
            const scores = this.students.map(s => {
              const markObj = s.marks.find(m => m.assessment === a.title);
              return markObj ? markObj.obtained : 0;
            });

            const total = scores.reduce((sum, s) => sum + s, 0);
            return scores.length ? parseFloat((total / scores.length).toFixed(2)) : 0;
          });

          this.lineChartData = {
            labels,
            datasets: [{
              label: 'Average Score',
              data: averages,
              borderColor: '#007bff',
              backgroundColor: 'rgba(0,123,255,0.2)',
              tension: 0.3,
              fill: true,
            }]
          };
        })
        .catch(err => {
          console.error('Failed to load analytics', err);
        });
    }
  }
};
</script>

