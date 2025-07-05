<template>
  <AppLayout :role="'Lecturer'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="m-0 fw-bold">📑 Manage Assessment</h5>
        <select v-model="courseId" class="form-select w-auto" @change="navigateToCourse">
            <option disabled value="">Select another course</option>
            <option v-for="c in courses" :key="c.id" :value="c.id">
            {{ c.course_code }} - {{ c.course_name }}
            </option>
        </select>
       </div>

      <!-- Assessment Card -->
      <div class="card shadow-sm">
        <div class="card-body">
          <!-- Total Weight -->
          <p><strong>Total Continuous Assessment Weight:</strong> <span class="badge rounded-pill text-bg-info">{{ totalWeight }}%</span> / 70%</p>

          <!-- Assessment Form -->
          <form @submit.prevent="submitAssessment" class="row g-3 mb-4">
            <div class="col-md-4">
              <input v-model="form.title" class="form-control" placeholder="Assessment Title" required />
            </div>
            <div class="col-md-3">
              <select v-model="form.type" class="form-select" required>
                <option disabled value="">Select Type</option>
                <option v-for="t in types" :key="t" :value="t">{{ t.charAt(0).toUpperCase() + t.slice(1) }}</option>
              </select>
            </div>
            <div class="col-md-2">
              <input v-model.number="form.max_mark" type="number" class="form-control" placeholder="Max Mark" required />
            </div>
            <div class="col-md-2">
              <input
                v-model.number="form.weight_percentage"
                type="number"
                class="form-control"
                placeholder="Weight (%)"
                :disabled="form.type === 'final'"
              />
            </div>
            <div class="col-md-1 d-grid">
              <button class="btn btn-dark" type="submit">
                {{ editingId ? 'Update' : 'Add' }}
              </button>
            </div>
          </form>

          <!-- Assessment List -->
          <table class="table table-bordered table-hover">
            <thead class="table-dark text-center">
              <tr>
                <th>#</th>
                <th>Title</th>
                <th>Type</th>
                <th>Max Mark</th>
                <th>Weight (%)</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(a, i) in assessments" :key="a.id" class="text-center">
                <td>{{ i + 1 }}</td>
                <td>{{ a.title }}</td>
                <td>{{ a.type.charAt(0).toUpperCase() + a.type.slice(1) }}</td>
                <td>{{ a.max_mark }}</td>
                <td>{{ a.weight_percentage }}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-warning me-1" @click="editAssessment(a)">Edit</button>
                </td>
              </tr>
            </tbody>
          </table>
          <div class="text-end mb-3">
            <button class="btn btn-outline-dark" @click="goToMarks">
                <i class="fas fa-pen"></i> Enter Marks for This Course
            </button>
          </div>
        </div>
      </div>

      <!-- Weight Exceeded Modal -->
      <div class="modal fade" id="weightExceededModal" tabindex="-1" aria-labelledby="weightExceededLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-warning">
            <div class="modal-header">
                <h5 class="modal-title" id="weightExceededLabel">Warning</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Total continuous assessment weight exceeds <strong>70%</strong>. Please adjust your weight values before adding more components.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancel</button>
            </div>
            </div>
        </div>
      </div>

      <!-- Toast Notification -->
      <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;" v-if="showingToast">
        <div class="toast align-items-center text-white border-0 show"
            :class="{ 'bg-success': toastType === 'success', 'bg-danger': toastType === 'danger' }">
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
  name: 'LecturerAssessments',
  components: { AppLayout },
  data() {
    return {
      user: JSON.parse(localStorage.getItem('user')),
      courseId: this.$route.params.id,
      assessments: [],
      form: {
        title: '',
        type: '',
        max_mark: '',
        weight_percentage: '',
        course: null,
      },
      editingId: null,
      types: ['quiz', 'assignment', 'lab', 'test', 'exercise', 'final'],
      navItems: [
        { name: 'Dashboard', link: '/lecturer/dashboard' },
        { name: 'My Courses', link: '/lecturer/courses' },
        { name: 'Student Enrollment', link: '/lecturer/students' },
        { name: 'Profile', link: '/lecturer/profile' }
      ],
      pageTitle: 'Assessments',
      courses: [],
      toastMessage: '',
      toastType: '',
      showingToast: false,
    };
  },
  computed: {
    totalWeight() {
      return this.assessments
        .filter(a => a.type !== 'final')
        .reduce((sum, a) => sum + parseFloat(a.weight_percentage), 0);
    }
  },
  mounted() {
    this.loadCourse();
    this.loadAssessments();
    this.loadCourses();
  },
  watch: {
    '$route.params.id': {
        handler(newId) {
        this.courseId = newId;
        this.loadCourse();
        this.loadAssessments();
        },
        immediate: false
    },
    'form.type'(val) {
        if (val === 'final') {
        this.form.weight_percentage = 30;
        }
    }
  },
  methods: {
    loadCourse() {
    api.get(`/lecturer/courses/${this.courseId}`)
        .then(res => {
        this.course = res.data.course;
        this.pageTitle = `Assessment (${this.course.course_code} - ${this.course.course_name})`;
        })
        .catch(() => {
        this.pageTitle = 'Assessment';
        });
    },
    loadCourses() {
        api.get('/lecturer/my-courses', {
            headers: {
            'X-User': JSON.stringify(this.user)
            }
        }).then(res => {
            this.courses = res.data.courses;
        });
        },
        navigateToCourse() {
        this.$router.push(`/lecturer/courses/${this.courseId}/assessments`);
    },
    loadAssessments() {
      api.get(`/lecturer/courses/${this.courseId}/assessments`)
        .then(res => this.assessments = res.data.assessments)
        .catch(() => alert('Failed to load assessments.'));
    },
    submitAssessment() {
      const data = { ...this.form };

      if (!this.editingId && this.totalWeight + parseFloat(data.weight_percentage) > 70 && data.type !== 'final') {
        const modal = new Modal(document.getElementById('weightExceededModal'));
        modal.show();
        return;
      }


      const url = `/lecturer/courses/${this.courseId}/assessments` + (this.editingId ? `/${this.editingId}` : '');
      const method = this.editingId ? 'put' : 'post';

      api[method](url, data, {
        headers: { 'X-User': JSON.stringify(this.user) }
      }).then(() => {
        const msg = this.editingId ? 'Assessment updated successfully' : 'Assessment added successfully';
        this.loadAssessments();
        this.resetForm();
        this.showToast(msg, 'success');
      })
    },
    editAssessment(a) {
      this.form = { ...a };
      this.editingId = a.id;
    },
    goToMarks() {
      this.$router.push(`/lecturer/courses/${this.courseId}/marks`);
    },
    resetForm() {
      this.editingId = null;
      this.form = { title: '', type: '', max_mark: '', weight_percentage: '' };
    },
    showToast(message, type = 'success') {
      this.toastMessage = message;
      this.toastType = type;
      this.showingToast = true;
      setTimeout(() => (this.showingToast = false), 3000);
    }
  }
};
</script>
