<template>
  <AppLayout :role="'Lecturer'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container">
      <!-- Course Selection -->
      <div class="d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold">🧑🏼‍🎓 Manage Students</h5>
        <select v-model="selectedCourseId" class="form-select w-auto" @change="loadStudents">
          <option disabled value="">Select course</option>
          <option v-for="course in courses" :key="course.id" :value="course.id">
            {{ course.course_code }} - {{ course.course_name }}
          </option>
        </select>
      </div>

      <div class="card p-4 shadow-sm">
        <!-- Add Student -->
        <label class="form-label">Add student in this course:</label>
        <div class="mb-3 input-group">
          <input v-model="newMatricNumber" class="form-control" placeholder="Enter matric no" />
          <button class="btn btn-dark" @click="addStudent" :disabled="!selectedCourseId"><i class="fa-solid fa-plus"></i> Add</button>
        </div>

        <!-- Search Bar -->
        <div class="mb-3 input-group">
          <span class="input-group-text bg-light">
            <i class="fas fa-search"></i>
          </span>
          <input v-model="searchQuery" class="form-control" placeholder="Search by name or matric no" />
        </div>

        <!-- Student Table -->
        <table class="table table-bordered table-hover border-dark">
          <thead class="table-dark">
            <tr class="text-center">
              <th>#</th><th>Name</th><th>Matric No.</th><th>Email</th><th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(student, index) in filteredStudents" :key="student.id" class="text-center">
              <td>{{ index + 1 }}</td>
              <td>{{ student.name }}</td>
              <td>{{ student.matric_number }}</td>
              <td>{{ student.email }}</td>
              <td><button class="btn btn-danger btn-sm" @click="removeStudent(student.id)">Remove</button></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Delete Confirmation Modal -->
      <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-danger">
            <div class="modal-header">
              <h5 class="modal-title" id="confirmDeleteLabel">Confirm Remove Student</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              Are you sure you want to remove this student from the course?
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-danger" @click="confirmRemoveStudent">Yes, Remove</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Message toast -->
      <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;" v-if="showingToast">
        <div class="toast align-items-center text-white border-0 show"
            :class="{ 'bg-success': toastType === 'success', 'bg-danger': toastType === 'danger', 'bg-warning': toastType === 'warning' }">
          <div class="d-flex">
            <div class="toast-body">
              {{ toastMessage }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="showingToast = false"></button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
import { Modal } from 'bootstrap';
import api from '@/api';

export default {
  name: 'LecturerManageStudents',
  components: { AppLayout },
  data() {
    return {
      user: JSON.parse(localStorage.getItem('user')),
      courses: [],
      selectedCourseId: '',
      students: [],
      newMatricNumber: '',
      searchQuery: '',
      navItems: [
        { name: 'Dashboard', link: '/lecturer/dashboard' },
        { name: 'My Courses', link: '/lecturer/courses' },
        { name: 'Student Enrollment', link: '/lecturer/students'},
        { name: 'Remark Requests', link: '/lecturer/remark-requests' },
        { name: 'Profile', link: '/lecturer/profile' }
      ],
      pageTitle: 'Student Enrollment',
      toastMessage: '',
      toastType: '',
      showingToast: false,
      studentToDelete: null,
    };
  },
  computed: {
    filteredStudents() {
      const q = this.searchQuery.toLowerCase();
      return this.students.filter(
        s =>
          s.name.toLowerCase().includes(q) ||
          s.matric_number.toLowerCase().includes(q)
      );
    }
  },
  mounted() {
    this.loadCourses();
  },
  methods: {
    loadCourses() {
      api.get('/lecturer/my-courses', {
        headers: {
          'X-User': JSON.stringify(this.user)
        }
      }).then(res => {
        this.courses = res.data.courses;
        if (this.courses.length > 0) {
          this.selectedCourseId = this.courses[0].id; // Auto-select first course
          this.loadStudents(); // Auto-load students
        }
      });
    },
    loadStudents() {
    if (!this.selectedCourseId) {
        console.warn("No course selected.");
        return;
    }
    api.get(`/lecturer/courses/${this.selectedCourseId}/students`)
        .then(res => (this.students = res.data.students))
        .catch(err => {
        console.error('Error loading students:', err);
        });
    },
    selectCourse(courseId) {
      this.selectedCourseId = courseId;
      this.loadStudents();
    },
    showToast(message, type = 'success') {
      this.toastMessage = message;
      this.toastType = type;
      this.showingToast = true;
      setTimeout(() => (this.showingToast = false), 3000);
    },
    addStudent() {
      if (!this.newMatricNumber || !this.selectedCourseId) return;

      api.post(`/lecturer/courses/${this.selectedCourseId}/students`, {
        matric_number: this.newMatricNumber
      }, {
        headers: {
          'Content-Type': 'application/json',
          'X-User': JSON.stringify(this.user)
        }
      })
      .then(() => {
        this.newMatricNumber = '';
        this.loadStudents();
        this.showToast('Student added successfully', 'success');
      })
      .catch(err => {
        this.showToast(err.response?.data?.error || 'Failed to add student', 'danger');
      });
    },
    removeStudent(studentId) {
      this.studentToDelete = studentId;
      const modal = new Modal(document.getElementById('confirmDeleteModal'));
      modal.show();
    },
    confirmRemoveStudent() {
      const studentId = this.studentToDelete;
      if (!studentId || !this.selectedCourseId) return;

      api.delete(`/lecturer/courses/${this.selectedCourseId}/students/${studentId}`)
        .then(() => {
          this.loadStudents();
          this.showToast('Student removed successfully', 'warning');
        })
        .catch(() => this.showToast('Error removing student', 'danger'))
        .finally(() => {
          const modal = Modal.getInstance(document.getElementById('confirmDeleteModal'));
          modal.hide();
          this.studentToDelete = null;
        });
    } 
  }
}
</script>

<style scoped>
.custom-active-tab {
  background-color: #fdd481 !important;
  color: #212529 !important; /* Bootstrap's text-dark */
  font-weight: bold;
  border-color: #dee2e6 #dee2e6 #fff; /* keep bottom active line clean */
}
</style>
