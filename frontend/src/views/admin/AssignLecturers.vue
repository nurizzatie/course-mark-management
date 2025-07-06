<template>
  <AppLayout :role="'Admin'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <!-- Header and Add Course Button -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Assign Lecturers to Courses</h2>
        <button @click="showAddModal = true" class="btn btn-primary">+ Add Course</button>
      </div>

      <!-- Add Course Modal -->
      <div v-if="showAddModal" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex justify-content-center align-items-center" style="z-index: 1050;">
        <div class="bg-white p-4 rounded shadow w-100" style="max-width: 500px;">
          <h5 class="mb-3">Add New Course</h5>
          <form @submit.prevent="submitNewCourse">
            <div class="mb-3">
              <label class="form-label">Course Code</label>
              <input v-model="newCourse.course_code" class="form-control" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Course Name</label>
              <input v-model="newCourse.course_name" class="form-control" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Semester</label>
              <input v-model="newCourse.semester" class="form-control" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Year</label>
              <input v-model="newCourse.year" type="number" class="form-control" required />
            </div>
            <div class="d-flex justify-content-end gap-2">
              <button type="button" @click="showAddModal = false" class="btn btn-secondary">Cancel</button>
              <button type="submit" class="btn btn-success">Add</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Assign Form -->
      <form @submit.prevent="assignLecturer" class="mb-4">
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

        <button class="btn btn-success">Assign Lecturer</button>
      </form>
    </div>
  </AppLayout>
</template>

<script>
import axios from 'axios'
import AppLayout from '@/layouts/AppLayout.vue'

export default {
  name: 'AssignLecturer',
  components: { AppLayout },
  data() {
    return {
      showAddModal: false,
      newCourse: {
        course_code: '',
        course_name: '',
        semester: '',
        year: new Date().getFullYear()
      },
      courses: [],
      lecturers: [],
      selectedCourse: '',
      selectedLecturer: '',
      navItems: [
        { name: 'Dashboard', link: '/admin/dashboard' },
        { name: 'Manage Users', link: '/admin/users' },
        { name: 'Assign Lecturers', link: '/admin/assign' },
        { name: 'Logs', link: '/admin/logs' },
        { name: 'Reset Password', link: '/admin/reset' }
      ],
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
      } catch (err) {
        console.error('Error loading data:', err)
        alert('Failed to load courses and lecturers.')
      }
    },
    async assignLecturer() {
      try {
        await axios.post('http://localhost:8080/api/admin/assign-lecturer', {
          course_id: this.selectedCourse,
          lecturer_id: this.selectedLecturer
        })
        alert('Lecturer successfully assigned to course!')
        this.selectedCourse = ''
        this.selectedLecturer = ''
      } catch (err) {
        console.error('Assignment error:', err)
        alert('Failed to assign lecturer.')
      }
    },
    async submitNewCourse() {
      try {
        await axios.post('http://localhost:8080/api/admin/courses', this.newCourse)
        alert('Course added successfully')
        this.showAddModal = false
        this.newCourse = {
          course_code: '',
          course_name: '',
          semester: '',
          year: new Date().getFullYear()
        }
        this.fetchData() // Refresh course list
      } catch (err) {
        alert('Failed to add course')
        console.error(err)
      }
    }
  }
}
</script>
