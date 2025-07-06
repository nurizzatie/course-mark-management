<template>
  <AppLayout :role="'Lecturer'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <!-- Add Button -->
      <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-dark" @click="openCreateModal">
          <i class="fas fa-plus me-1"></i> Add Course
        </button>
      </div>

      <!-- Course Cards -->
      <div class="row g-3">
        <div class="col-md-4 col-sm-6" v-for="course in courses" :key="course.id">
          <div class="card shadow-sm" :style="{ backgroundColor: '#ebe0e3' }">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title fw-semibold" :style="{ color: '#5D001D' }">
                {{ course.course_code }} - {{ course.course_name }}
              </h5>
              <p class="card-text mb-3"><strong>Semester:</strong> {{ course.semester }}-{{ course.year }}</p>

              <div class="mt-auto d-flex justify-content-between">
                <div>
                  <button class="btn btn-outline-dark btn-sm mx-2" @click="openEditModal(course)">
                  <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-outline-danger btn-sm" @click="confirmDelete(course)">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
                
                <router-link
                  :to="`/lecturer/courses/${course.id}/assessments`"
                  class="btn btn-dark btn-sm"
                >
                  <i class="fas fa-tasks me-1"></i> Manage Assessment
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Add/Edit Course Modal -->
      <div class="modal fade" id="courseModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header bg-dark text-white">
              <h5 class="modal-title">{{ isEdit ? 'Edit Course' : 'Add Course' }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <input v-model="courseForm.course_code" class="form-control mb-2" placeholder="Course Code" />
              <input v-model="courseForm.course_name" class="form-control mb-2" placeholder="Course Name" />
              <input v-model="courseForm.semester" class="form-control mb-2" placeholder="Semester" />
              <input v-model="courseForm.year" type="number" class="form-control mb-2" placeholder="Year" />
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button class="btn btn-dark" @click="submitCourse">{{ isEdit ? 'Update' : 'Create' }}</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Confirm Modal -->
      <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content border-danger">
            <div class="modal-header">
              <h5 class="modal-title">Confirm Delete</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              Are you sure you want to delete course <strong>{{ courseToDelete?.course_code }}</strong>?
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button class="btn btn-danger" @click="deleteCourse">Yes, Delete</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Toast message -->  
      <div class="toast-container position-fixed bottom-0 end-0 p-3" v-if="showingToast" style="z-index: 9999">
        <div class="toast show text-white" :class="{ 'bg-success': toastType === 'success', 'bg-warning': toastType === 'warning', 'bg-danger': toastType === 'danger' }">
          <div class="d-flex">
            <div class="toast-body">{{ toastMessage }}</div>
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
import { nextTick } from 'vue';
import api from '@/api';

export default {
  name: 'LecturerCourses',
  components: { AppLayout },
  data() {
    return {
      user: JSON.parse(localStorage.getItem('user')),
      courses: [],
      navItems: [
        { name: 'Dashboard', link: '/lecturer/dashboard' },
        { name: 'My Courses', link: '/lecturer/courses', active: true },
        { name: 'Student Enrollment', link: '/lecturer/students' },
        { name: 'Remark Requests', link: '/lecturer/remark-requests' },
        { name: 'Profile', link: '/lecturer/profile' }
      ],
      pageTitle: 'My Courses',
      showModal: false,
      isEdit: false,
      courseForm: {
        id: null,
        course_code: '',
        course_name: '',
        semester: '',
        year: ''
      },
      courseToDelete: null,
      toastMessage: '',
      toastType: '',
      showingToast: false,
    };
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
      }).catch(() => {
        alert('Failed to load courses.');
      });
    },
    openCreateModal() {
      this.isEdit = false;
      this.courseForm = { id: null, course_code: '', course_name: '', semester: '', year: '' };

      nextTick(() => {
        const modalEl = document.getElementById('courseModal');
        const modalInstance = Modal.getInstance(modalEl) || new Modal(modalEl);
        modalInstance.show();
      });
    },
    openEditModal(course) {
      this.isEdit = true;
      this.courseForm = { ...course };
      new Modal(document.getElementById('courseModal')).show();
    },
    submitCourse() {
      const { course_code, course_name, semester, year } = this.courseForm;

      // Validation check
      if (!course_code || !course_name || !semester || !year) {
        this.showToast('Please fill in all fields.', 'danger');
        return;
      }

      const method = this.isEdit ? 'put' : 'post';
      const url = this.isEdit
        ? `/lecturer/courses/${this.courseForm.id}`
        : '/lecturer/courses';

      api[method](url, this.courseForm, {
        headers: {
          'Content-Type': 'application/json',
          'X-User': JSON.stringify(this.user)
        }
      })
        .then(() => {
          this.loadCourses();
          const modalEl = document.getElementById('courseModal');
          const modalInstance = Modal.getInstance(modalEl) || new Modal(modalEl);
          modalInstance.hide();
          this.showToast(this.isEdit ? 'Course updated successfully' : 'Course created successfully');
        })
        .catch(() => {
          this.showToast('Failed to save course.', 'danger');
        });
    },
    confirmDelete(course) {
      this.courseToDelete = course;
      new Modal(document.getElementById('deleteConfirmModal')).show();
    },
    deleteCourse() {
      api.delete(`/lecturer/courses/${this.courseToDelete.id}`, {
        headers: {
          'X-User': JSON.stringify(this.user)
        }
      }).then(() => {
          this.loadCourses();
          const modalEl = document.getElementById('deleteConfirmModal');
          const modalInstance = Modal.getInstance(modalEl);
          modalInstance.hide();
          this.showToast('Course deleted successfully', 'warning');
        });
    },
    showToast(msg, type = 'success') {
      this.toastMessage = msg;
      this.toastType = type;
      this.showingToast = true;
      setTimeout(() => this.showingToast = false, 3000);
    }
  }
}
</script>