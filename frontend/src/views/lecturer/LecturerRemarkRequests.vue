<template>
  <AppLayout :role="'Lecturer'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <h5 class="fw-bold mb-3">📬 Remark Requests</h5>

      <table class="table table-bordered align-middle text-center">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Student</th>
            <th>Assessment</th>
            <th>Course</th>
            <th>Justification</th>
            <th>Supporting Link</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(req, i) in requests" :key="req.id">
            <td>{{ i + 1 }}</td>
            <td>{{ req.student_name }}<br><small>{{ req.matric_number }}</small></td>
            <td>{{ req.assessment_title }}</td>
            <td>{{ req.course_code }}</td>
            <td>{{ req.justification }}</td>
            <td>
              <a v-if="req.supporting_link" :href="req.supporting_link" target="_blank">View</a>
              <span v-else>-</span>
            </td>
            <td>
              <span class="badge"
                :class="{
                  'bg-warning': req.status === 'pending',
                  'bg-info': req.status === 'reviewed',
                  'bg-success': req.status === 'approved',
                  'bg-danger': req.status === 'rejected'
                }">
                {{ req.status }}
              </span>
            </td>
            <td>
              <div v-if="req.status === 'pending'">
                <button class="btn btn-sm btn-success me-1" @click="respond(req.id, 'reviewed')">
                  Reviewed
                </button>
              </div>
              <div v-else-if="req.status === 'reviewed'">
                <button class="btn btn-sm btn-success me-1" @click="respond(req.id, 'approved')">
                  Accept
                </button>
                <button class="btn btn-sm btn-danger" @click="respond(req.id, 'rejected')">
                  Reject
                </button>
              </div>
              <span v-else>-</span>
            </td>
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
  name: 'LecturerRemarkRequests',
  components: { AppLayout },
  data() {
    return {
      navItems: [
        { name: 'Dashboard', link: '/lecturer/dashboard' },
        { name: 'My Courses', link: '/lecturer/courses' },
        { name: 'Student Enrollment', link: '/lecturer/students' },
        { name: 'Remark Requests', link: '/lecturer/remark-requests' },
        { name: 'Profile', link: '/lecturer/profile' }
      ],
      pageTitle: 'Remark Requests',
      requests: []
    };
  },
  mounted() {
    this.loadRequests();
  },
  methods: {
    loadRequests() {
      api.get('/lecturer/remark-requests', {
        headers: {
          'X-User': localStorage.getItem('user')
        }
      }).then(res => {
        this.requests = res.data.requests;
      });
    },
    respond(id, status) {
      api.put(`/lecturer/remark-requests/${id}`, { status })
        .then(() => this.loadRequests())
        .catch(() => alert('Failed to update request.'));
    }
  }
};
</script>
