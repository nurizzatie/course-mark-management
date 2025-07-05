// ✅ AssignLecturers.vue (Updated)
<template>
  <AppLayout :role="'Admin'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <h2 class="mb-4 fw-bold">Assign Lecturers to Courses</h2>

      <form @submit.prevent="assignLecturer" class="mb-4 needs-validation" novalidate>
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="course" class="form-label">Select Course</label>
            <select id="course" v-model="selectedCourse" class="form-select" required>
              <option disabled value="">-- Select Course --</option>
              <option v-for="course in courses" :key="course.id" :value="course.id">
                {{ course.course_code }} - {{ course.course_name }}
              </option>
            </select>
          </div>

          <div class="col-md-6">
            <label for="lecturer" class="form-label">Select Lecturer</label>
            <select id="lecturer" v-model="selectedLecturer" class="form-select" required>
              <option disabled value="">-- Select Lecturer --</option>
              <option v-for="lecturer in lecturers" :key="lecturer.id" :value="lecturer.id">
                {{ lecturer.name }}
              </option>
            </select>
          </div>
        </div>

        <button class="btn btn-success" :disabled="loading">
          {{ loading ? 'Assigning...' : 'Assign Lecturer' }}
        </button>
      </form>

      <!-- Optional: Simple Chart -->
      <div class="mt-5">
        <canvas id="assignmentChart" height="100"></canvas>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import axios from 'axios'
import AppLayout from '@/layouts/AppLayout.vue'
import Chart from 'chart.js/auto'
import adminNavItems from '@/constants/adminNavItems'

export default {
  name: 'AssignLecturers',
  components: { AppLayout },
  data() {
    return {
      courses: [],
      lecturers: [],
      selectedCourse: '',
      selectedLecturer: '',
      loading: false,
      navItems: adminNavItems,
      pageTitle: 'Assign Lecturers'
    }
  },
  mounted() {
    this.fetchData()
  },
  methods: {
    async fetchData() {
      try {
        const res = await axios.get('http://localhost:8080/api/admin/assign-data')
        this.courses = res.data.courses
        this.lecturers = res.data.lecturers
        this.renderChart()
      } catch (err) {
        console.error('Error loading data:', err)
        alert('Failed to load courses and lecturers.')
      }
    },
    async assignLecturer() {
      if (!this.selectedCourse || !this.selectedLecturer) {
        alert('Please select both course and lecturer.')
        return
      }

      this.loading = true
      try {
        await axios.post('http://localhost:8080/api/admin/assign-lecturer', {
          course_id: this.selectedCourse,
          lecturer_id: this.selectedLecturer
        })
        alert('Lecturer successfully assigned to course!')
        this.selectedCourse = ''
        this.selectedLecturer = ''
        this.fetchData() // refresh data
      } catch (err) {
        console.error('Assignment error:', err)
        alert('Failed to assign lecturer.')
      } finally {
        this.loading = false
      }
    },
    renderChart() {
      const ctx = document.getElementById('assignmentChart')
      if (ctx) {
        new Chart(ctx, {
          type: 'bar',
          data: {
            labels: this.courses.map(c => c.course_code),
            datasets: [{
              label: 'Assigned Courses (Static Preview)',
              data: this.courses.map(() => Math.floor(Math.random() * 2)), // fake 0/1 assigned
              backgroundColor: '#28a745'
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: { display: false }
            }
          }
        })
      }
    }
  }
}
</script>

<style scoped>
label {
  font-weight: 600;
}
</style>
