<template>
  <AppLayout :role="'Advisor'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <!-- Title & Download -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold">🗒️ Consultation Log and Report</h5>
        <button class="btn btn-primary" @click="downloadNotes">
          ⬇️ Download Report
        </button>
      </div>

      <!-- Form Section -->
      <div class="card p-4 mb-4 shadow-sm">
        <label class="form-label">Student</label>
        <select v-model="form.student_id" class="form-select mb-3" required>
          <option disabled value="">Select student</option>
          <option v-for="student in students" :key="student.id" :value="student.id">
            {{ student.name }} ({{ student.matric_number }})
          </option>
        </select>

        <label class="form-label">Consultation Date</label>
        <input type="date" v-model="form.meeting_date" class="form-control mb-3" required />

        <label class="form-label">Remark</label>
        <textarea v-model="form.note" class="form-control mb-3" rows="4" required></textarea>

        <button class="btn btn-dark" @click="submitNote" :disabled="loading">
          {{ loading ? 'Saving...' : 'Add Note' }}
        </button>
      </div>

      <!-- Notes Table -->
      <div class="card shadow-sm">
        <div class="card-header bg-dark text-white fw-bold">
          Consultation Notes
        </div>
        <div class="card-body table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="table-dark text-center">
              <tr>
                <th>#</th>
                <th>Student</th>
                <th>Matric No</th>
                <th>Date</th>
                <th>Note</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(note, index) in notes" :key="note.id" class="text-center">
                <td>{{ index + 1 }}</td>
                <td>{{ note.student_name }}</td>
                <td>{{ note.matric_number }}</td>
                <td>{{ note.meeting_date }}</td>
                <td class="text-start">{{ note.note }}</td>
              </tr>
            </tbody>
          </table>
          <p v-if="!notes.length" class="text-muted text-center py-3">No consultation notes found.</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
import api from '@/api';

export default {
  name: 'AdvisorNotes',
  components: { AppLayout },
  data() {
    return {
      pageTitle: 'Consultation',
      notes: [],
      students: [],
      loading: false,
      form: {
        student_id: '',
        meeting_date: '',
        note: ''
      },
      navItems: [
        { name: 'Dashboard', link: '/advisor/dashboard' },
        { name: 'Advisees', link: '/advisor/students' },
        { name: 'Mark Review', link: '/advisor/reviews' },
        { name: 'Performance Analytics', link: '/advisor/analytics' },
        { name: 'Consultation', link: '/advisor/notes' },
        { name: 'Profile', link: '/advisor/profile' }
      ]
    };
  },
  methods: {
    async fetchNotes() {
      const advisor = JSON.parse(localStorage.getItem('user'));
      if (!advisor?.id) return;
      try {
        const res = await api.get(`/advisor/notes?advisor_id=${advisor.id}`);
        this.notes = res.data;
      } catch (err) {
        console.error('Fetch notes error:', err);
      }
    },
    async fetchStudents() {
      try {
        const res = await api.get('/advisor/students');
        this.students = res.data;
      } catch (err) {
        console.error('Fetch students error:', err);
      }
    },
    async submitNote() {
      const advisor = JSON.parse(localStorage.getItem('user'));
      if (!advisor?.id) return;
      try {
        this.loading = true;
        await api.post('/advisor/notes', {
          advisor_id: advisor.id,
          ...this.form
        });
        this.form = { student_id: '', meeting_date: '', note: '' };
        this.fetchNotes();
      } catch (err) {
        alert('Failed to add note.');
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
    downloadNotes() {
      if (!this.notes.length) return alert("No notes to download.");

      const header = ["Student Name", "Matric Number", "Meeting Date", "Note"];
      const rows = this.notes.map(n =>
        [n.student_name, n.matric_number, n.meeting_date, `"${n.note.replace(/"/g, '""')}"`]
      );

      const csv = [header.join(','), ...rows.map(r => r.join(','))].join('\n');
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.setAttribute('download', 'advisor_notes.csv');
      link.click();
    }
  },
  mounted() {
    this.fetchNotes();
    this.fetchStudents();
  }
};
</script>






