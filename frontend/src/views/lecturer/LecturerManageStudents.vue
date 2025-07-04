<template>
  <AppLayout :role="'Lecturer'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container">
      <!-- Course Selection -->
      <div class="mb-4">
        <label class="form-label">Select Course</label>
        <select v-model="selectedCourseId" class="form-select" @change="loadStudents">
          <option disabled value="">-- Select a course --</option>
          <option v-for="course in courses" :key="course.id" :value="course.id">
            {{ course.course_code }} - {{ course.course_name }}
          </option>
        </select>
      </div>

      <!-- Search Bar -->
      <div class="mb-3">
        <input v-model="searchQuery" class="form-control" placeholder="Search by name or matric no" />
      </div>

      <!-- Add Student -->
      <div class="mb-3 input-group">
        <input v-model="newStudentId" class="form-control" placeholder="Enter student ID to add" />
        <button class="btn btn-success" @click="addStudent" :disabled="!selectedCourseId">Add</button>
      </div>

      <!-- Student Table -->
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>#</th><th>Name</th><th>Matric</th><th>Email</th><th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(student, index) in filteredStudents" :key="student.id">
            <td>{{ index + 1 }}</td>
            <td>{{ student.name }}</td>
            <td>{{ student.matric_number }}</td>
            <td>{{ student.email }}</td>
            <td><button class="btn btn-danger btn-sm" @click="removeStudent(student.id)">Remove</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
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
      newStudentId: '',
      searchQuery: '',
      navItems: [
        { name: 'Dashboard', link: '/lecturer/dashboard', active: false },
        { name: 'My Courses', link: '/lecturer/courses', active: false },
        { name: 'Student List', link: '/lecturer/students', active: false },
        { name: 'Profile', link: '/lecturer/profile', active: true }
      ],
      pageTitle: 'Manage Students',
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
    addStudent() {
        if (!this.newStudentId || !this.selectedCourseId) return;

        api.post(`/lecturer/courses/${this.selectedCourseId}/students`, {
            student_id: this.newStudentId
        })
        .then(() => {
            this.newStudentId = '';
            this.loadStudents();
        })
        .catch(err => alert(err.response?.data?.error || 'Error adding student'));
    },
    removeStudent(studentId) {
        if (!confirm('Are you sure?') || !this.selectedCourseId) return;

        api.delete(`/lecturer/courses/${this.selectedCourseId}/students/${studentId}`)
            .then(() => this.loadStudents())
            .catch(() => alert('Error removing student'));
    }
  }
}
</script>
