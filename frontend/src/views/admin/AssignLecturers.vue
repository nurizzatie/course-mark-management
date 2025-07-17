<template>
  <AppLayout :role="'Admin'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <!-- Header and Add Course Button -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Assign Lecturers to Courses</h2>
      </div>

      <!-- Toasts -->
      <div v-if="toast.message" :class="['alert', toast.type === 'success' ? 'alert-success' : 'alert-danger']" role="alert">
        {{ toast.message }}
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
              <button type="submit" class="btn btn-success" :disabled="loading">Add</button>
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
              <option v-for="course in courses" :key="course.course_id" :value="course.course_id">
                {{ course.course_code }} - {{ course.course_name }}
              </option>
            </select>
          </div>

          <div class="col-md-6">
            <label for="lecturer" class="form-label">Select Lecturer</label>
            <select id="lecturer" v-model="selectedLecturer" class="form-select" required>
              <option disabled value="">-- Select Lecturer --</option>
              <option v-for="lecturer in lecturers" :key="lecturer.lecturer_id" :value="lecturer.lecturer_id">
                {{ lecturer.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="d-flex gap-2 mt-3">
  <button class="btn btn-success" :disabled="loading">
    <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
    Assign Lecturer
  </button>

  <button type="button" @click="showAddModal = true" class="btn btn-outline-primary">
    + Add Course
  </button>
</div>

      </form>

      <!-- Assigned Lecturers Table -->
      <div v-if="assignedLectures.length">
        <h5 class="fw-bold mt-4 mb-3">Assigned Lecturers to Courses</h5>
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="table-light">
              <tr>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Lecturer</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="assign in assignedLectures" :key="assign.course_id">
                <td>{{ assign.course_code }}</td>
                <td>{{ assign.course_name }}</td>
                <td>{{ assign.lecturer_name }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import axios from 'axios'
import AppLayout from '@/layouts/AppLayout.vue'
import adminNavItems from '@/constants/adminNavItems'

export default {
  name: 'AssignLecturer',
  components: { AppLayout },
  data() {
    return {
      navItems: adminNavItems,
      showAddModal: false,
      loading: false,
      toast: { message: '', type: 'success' },
      newCourse: {
        course_code: '',
        course_name: '',
        semester: '',
        year: new Date().getFullYear()
      },
      courses: [],
      lecturers: [],
      assignedLectures: [],
      selectedCourse: '',
      selectedLecturer: '',
      pageTitle: 'Assign Lecturers'
    }
  },
  mounted() {
    this.fetchData()
  },
  methods: {
    async fetchData() {
      try {
        const res = await axios.get(`${process.env.VUE_APP_API_URL}/api/admin/assign-data`)
        this.courses = res.data.courses
        this.lecturers = res.data.lecturers
        this.assignedLectures = res.data.assignments || []
      } catch (err) {
        this.showToast('Failed to load data', 'danger')
        console.error(err)
      }
    },
    async assignLecturer() {
      if (!this.selectedCourse || !this.selectedLecturer) {
        this.showToast('Please select both course and lecturer', 'danger')
        return
      }
      this.loading = true
      try {
        await axios.post(`${process.env.VUE_APP_API_URL}/api/admin/assign-lecturer-direct`, {
          lecturer_id: this.selectedLecturer,
          course_id: this.selectedCourse
        })
        this.showToast('Lecturer assigned successfully', 'success')
        this.selectedCourse = ''
        this.selectedLecturer = ''
        this.fetchData()
      } catch (err) {
        this.showToast('Failed to assign lecturer', 'danger')
        console.error(err)
      } finally {
        this.loading = false
      }
    },
    async submitNewCourse() {
      this.loading = true
      try {
        await axios.post(`${process.env.VUE_APP_API_URL}/api/admin/courses`, this.newCourse)
        this.showToast('Course added successfully', 'success')
        this.showAddModal = false
        this.newCourse = {
          course_code: '',
          course_name: '',
          semester: '',
          year: new Date().getFullYear()
        }
        this.fetchData()
      } catch (err) {
        this.showToast('Failed to add course', 'danger')
        console.error(err)
      } finally {
        this.loading = false
      }
    },
    showToast(message, type = 'success') {
      this.toast.message = message
      this.toast.type = type
      setTimeout(() => {
        this.toast.message = ''
      }, 3000)
    }
  }
}
</script>

<style scoped>
.alert {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  min-width: 250px;
}
</style>
