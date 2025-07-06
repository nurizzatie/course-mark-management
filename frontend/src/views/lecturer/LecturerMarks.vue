<template>
  <AppLayout :role="'Lecturer'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="m-0 fw-bold">📝 Enter Student Marks</h5>
        <select v-model="courseId" class="form-select w-auto" @change="navigateToCourse">
            <option disabled value="">Select another course</option>
            <option v-for="c in courses" :key="c.id" :value="c.id">
            {{ c.course_code }} - {{ c.course_name }}
            </option>
        </select>
      </div>
      <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
          <div>
            <button class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#importCsvModal">
              <i class="fas fa-file-import me-1"></i> Import
            </button>
            <button class="btn btn-outline-light" @click="exportToCSV">
              <i class="fas fa-file-export me-1"></i> Export
            </button>
          </div>
          <div>
            <button
              class="btn btn-light me-2"
              v-if="editMode"
              @click="toggleEdit"
            >
              <i class="fas fa-save me-1"></i> Save Changes
            </button>
            <button
              class="btn btn-secondary"
              v-if="editMode"
              @click="cancelEdit"
            >
              <i class="fas fa-times me-1"></i> Cancel
            </button>
            <button
              class="btn btn-outline-light"
              v-else
              @click="toggleEdit"
            >
              <i class="fas fa-edit me-1"></i> Edit Marks
            </button>
          </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-dark">
                      <tr>
                        <th>#</th>
                        <th>Matric No.</th>
                        <th v-for="a in assessments" :key="a.id">{{ a.title }}</th>
                        <th>Total (%)</th>
                        <th>Grade</th>
                        <th>Remarks</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(student, i) in students" :key="student.id">
                        <td>{{ i + 1 }}</td>
                        <td>{{ student.matric_number }}</td>
                        <td v-for="a in assessments" :key="a.id">
                          <template v-if="editMode">
                            <input
                              type="number"
                              class="form-control"
                              :max="a.max_mark"
                              :step="0.01"
                              v-model.number="marksMap[`${student.id}_${a.id}`]"
                            />
                          </template>
                          <template v-else>
                            {{ marksMap[`${student.id}_${a.id}`] ?? '-' }}
                          </template>
                        </td>
                        <td>{{ getTotal(student.id) }}</td>
                        <td>{{ getGrade(student.id) }}</td>
                        <td>
                          <template v-if="editMode">
                            <input type="text" class="form-control" v-model="remarksMap[student.id]" />
                          </template>
                          <template v-else>
                            {{ remarksMap[student.id] || '-' }}
                          </template>
                        </td>
                      </tr>
                    </tbody>
                </table>
            </div>
        </div>
      </div>
      
      <!-- Import CSV Modal -->
      <div class="modal fade" id="importCsvModal" tabindex="-1" aria-labelledby="importCsvLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-primary">
            <div class="modal-header">
              <h5 class="modal-title" id="importCsvLabel">Import Marks via CSV</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <label for="csvFile" class="form-label">Choose CSV File</label>
              <input type="file" class="form-control" @change="handleFileUpload" accept=".csv" />
            </div>
          </div>
        </div>
      </div>

      <!-- Save Confirmation Modal -->
      <div class="modal fade" id="confirmSaveModal" tabindex="-1" aria-labelledby="confirmSaveLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-success">
            <div class="modal-header">
              <h5 class="modal-title" id="confirmSaveLabel">Confirm Save</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              Are you sure you want to save the changes?
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-dark" @click="confirmSave">Yes, Save</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Cancel Confirmation Modal -->
      <div class="modal fade" id="confirmCancelModal" tabindex="-1" aria-labelledby="confirmCancelLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-danger">
            <div class="modal-header">
              <h5 class="modal-title" id="confirmCancelLabel">Discard Changes</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              Are you sure you want to discard your changes?
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
              <button type="button" class="btn btn-danger" @click="confirmCancel">Yes, Discard</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Toast Notification -->
      <div class="toast-container position-fixed bottom-0 end-0 p-3" v-if="showingToast" style="z-index: 9999">
        <div class="toast show text-white bg-success border-0" :class="{ 'bg-success': toastType === 'success','bg-danger': toastType === 'danger' }">
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
import api from '@/api';

export default {
  name: 'LecturerMarks',
  components: { AppLayout },
  data() {
    return {
      user: JSON.parse(localStorage.getItem('user')),
      courseId: this.$route.params.id,
      students: [],
      assessments: [],
      marksMap: {},
      originalMarksMap: {},
      remarksMap: {},
      toastMessage: '',
      showingToast: false,
      navItems: [
        { name: 'Dashboard', link: '/lecturer/dashboard' },
        { name: 'My Courses', link: '/lecturer/courses' },
        { name: 'Student Enrollment', link: '/lecturer/students' },
        { name: 'Remark Requests', link: '/lecturer/remark-requests' },
        { name: 'Profile', link: '/lecturer/profile' }
      ],
      pageTitle: 'Assessment Marks',
      courses: [],
      totals: [],
      editMode: false,
    };
  },
  mounted() {
    this.loadMarks();
    this.loadCourses();
  },
  watch: {
    '$route.params.id': {
        handler(newId) {
        this.courseId = newId;
        this.loadMarks();
        },
        immediate: false
    },
  },
  methods: {
    loadMarks() {
      api.get(`/lecturer/courses/${this.courseId}/marks`)
        .then(res => {
          this.students = res.data.students;
          this.assessments = res.data.assessments;

          // Pre-fill marksMap
          this.marksMap = {};
          for (const mark of res.data.marks) {
            const key = `${mark.student_id}_${mark.assessment_id}`;
            this.marksMap[key] = mark.obtained_mark;
          }
          // Backup original marks
          this.originalMarksMap = JSON.parse(JSON.stringify(this.marksMap));
          this.totals = res.data.totals || [];
          this.remarksMap = res.data.remarks || {};
        })
        .catch(() => alert('Failed to load mark data.'));
    },
    saveMarks() {
      const payload = [];
      let hasError = false;

      for (const s of this.students) {
        for (const a of this.assessments) {
          const key = `${s.id}_${a.id}`;
          const mark = this.marksMap[key];

          if (mark !== undefined && mark !== null && mark !== '') {
            if (parseFloat(mark) > a.max_mark) {
              this.showToast(`❌ ${s.matric_number} - ${a.title} exceeds max (${a.max_mark})`, 'danger');
              this.marksMap[key] = '';
              hasError = true;
            } else {
              payload.push({
                student_id: s.id,
                assessment_id: a.id,
                obtained_mark: mark
              });
            }
          }
        }
      }

      if (hasError) return;

      api.post(`/lecturer/courses/${this.courseId}/marks`, { marks: payload, remarks: this.remarksMap })
        .then(() => {
          this.showToast('Marks saved successfully', 'success');
          this.loadMarks();
        })
        .catch(() => this.showToast('Failed to save marks', 'danger'));
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
        this.$router.push(`/lecturer/courses/${this.courseId}/marks`);
    },
    showToast(msg, type = 'success') {
        this.toastMessage = msg;
        this.toastType = type;
        this.showingToast = true;
        setTimeout(() => (this.showingToast = false), 3000);
    },
    toggleEdit() {
      if (this.editMode) {
        const saveModal = new Modal(document.getElementById('confirmSaveModal'));
        saveModal.show();
      } else {
        this.editMode = true;
      }
    },
    cancelEdit() {
      const cancelModal = new Modal(document.getElementById('confirmCancelModal'));
      cancelModal.show();
    },
    confirmSave() {
      const modal = Modal.getInstance(document.getElementById('confirmSaveModal'));
      modal.hide();
      this.saveMarks();
      this.editMode = false;
    },
    confirmCancel() {
      const modal = Modal.getInstance(document.getElementById('confirmCancelModal'));
      modal.hide();
      this.marksMap = JSON.parse(JSON.stringify(this.originalMarksMap));
      this.editMode = false;
    },
    getTotal(studentId) {
      const row = this.totals.find(t => t.student_id === studentId);
      return row ? row.total_mark.toFixed(2) : '-';
    },
    getGrade(studentId) {
      const row = this.totals.find(t => t.student_id === studentId);
      return row ? row.grade : '-';
    },
    handleFileUpload(e) {
      const file = e.target.files[0];
      if (!file) return;

      const formData = new FormData();
      formData.append('file', file); // key must be "file"
      console.log('Uploading file:', file.name);

      api.post(`/lecturer/courses/${this.courseId}/marks/upload`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data' // required
        }
      })
        .then(() => {
          this.showToast('CSV uploaded successfully', 'success');
          this.loadMarks();
          const modal = Modal.getInstance(document.getElementById('importCsvModal'));
          modal.hide();
        })
        .catch(() => this.showToast('Failed to upload CSV', 'danger'));
    },
    exportToCSV() {
      const headers = ['Matric No', ...this.assessments.map(a => a.title), 'Total', 'Grade', 'Remarks'];
      const rows = this.students.map(student => {
        const id = student.id;
        const row = [
          student.matric_number,
          ...this.assessments.map(a => this.marksMap[`${id}_${a.id}`] ?? ''),
          this.getTotal(id),
          this.getGrade(id),
          this.remarksMap[id] ?? ''
        ];
        return row.join(',');
      });

      const csvContent = [headers.join(','), ...rows].join('\n');
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `marks_course_${this.courseId}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  }
};
</script>
