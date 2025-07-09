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
        <label class="form-label">Select Student</label>
        <div class="position-relative mb-3">
          <input
            type="text"
            class="form-control"
            placeholder="Type to search by matric number..."
            v-model="form.student_matric_number"
            @focus="showDropdown = true"
            @blur="hideDropdown"
            @input="showDropdown = true"
          />
          <ul
            v-if="showDropdown && filteredStudents.length"
            class="list-group position-absolute w-100 shadow"
            style="z-index: 10; max-height: 200px; overflow-y: auto"
          >
            <li
              class="list-group-item list-group-item-action"
              v-for="student in filteredStudents"
              :key="student.id"
              @mousedown.prevent="selectStudent(student)"
            >
              {{ student.matric_number }} - {{ student.name }}
            </li>
          </ul>
        </div>

        <label class="form-label">Consultation Date</label>
        <input
          type="date"
          v-model="form.meeting_date"
          class="form-control mb-3"
          required
        />

        <label class="form-label">Remark</label>
        <textarea
          v-model="form.note"
          class="form-control mb-3"
          rows="4"
          required
        ></textarea>

        <button
          class="btn btn-dark"
          @click="submitNote"
          :disabled="loading"
        >
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
              <tr
                v-for="(note, index) in notes"
                :key="note.id"
                class="text-center"
              >
                <td>{{ index + 1 }}</td>
                <td>{{ note.student_name }}</td>
                <td>{{ note.matric_number }}</td>
                <td>{{ note.meeting_date }}</td>
                <td class="text-start">{{ note.note }}</td>
              </tr>
            </tbody>
          </table>
          <p v-if="!notes.length" class="text-muted text-center py-3">
            No consultation notes found.
          </p>
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
      showDropdown: false,
      form: {
        student_matric_number: '',
        meeting_date: '',
        note: '',
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
  computed: {
    filteredStudents() {
      const q = this.form.student_matric_number.toLowerCase().trim();
      return this.students.filter(s =>
        s.matric_number.toLowerCase().includes(q)
      );
    }
  },
  methods: {
    async fetchNotes() {
      const advisor = JSON.parse(localStorage.getItem('user'));
      if (!advisor?.id) return;
      try {
        const res = await api.get(`/advisor/notes?advisor_id=${advisor.id}`);
        this.notes = res.data;
      } catch (err) {
        console.error('Error fetching notes:', err);
      }
    },
    async fetchStudents() {
  const advisor = JSON.parse(localStorage.getItem('user'));
  if (!advisor?.id) return;

  try {
    const res = await api.get('/advisor/students', {
      headers: {
        'X-User': JSON.stringify(advisor)
      }
    });
    this.students = res.data;
  } catch (err) {
    console.error('Error fetching students:', err);
  }
},

    selectStudent(student) {
      this.form.student_matric_number = student.matric_number;
      this.showDropdown = false;
    },
    hideDropdown() {
      setTimeout(() => {
        this.showDropdown = false;
      }, 200); // delay allows click to register
    },
    async submitNote() {
      const advisor = JSON.parse(localStorage.getItem('user'));
      if (!advisor?.id) return;

      const { student_matric_number, meeting_date, note } = this.form;
      if (!student_matric_number || !meeting_date || !note) {
        alert("Please fill in all fields.");
        return;
      }

      const matchedStudent = this.students.find(
        s => s.matric_number.toLowerCase() === student_matric_number.toLowerCase()
      );

      if (!matchedStudent) {
        alert('Student with that matric number not found.');
        return;
      }

      try {
        this.loading = true;
        await api.post('/advisor/notes', {
          advisor_id: advisor.id,
          student_id: matchedStudent.id,
          meeting_date,
          note
        });

        this.form = {
          student_matric_number: '',
          meeting_date: '',
          note: ''
        };

        await this.fetchNotes();
      } catch (err) {
        alert('Failed to add note.');
        console.error('Submit error:', err);
      } finally {
        this.loading = false;
      }
    },
    downloadNotes() {
      if (!this.notes.length) return alert('No notes to download.');

      const header = ['Student Name', 'Matric Number', 'Meeting Date', 'Note'];
      const rows = this.notes.map(note => [
        `"${note.student_name}"`,
        `"${note.matric_number}"`,
        `"${note.meeting_date}"`,
        `"${note.note.replace(/"/g, '""')}"`
      ]);

      const csv = [header.join(','), ...rows.map(r => r.join(','))].join('\n');
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);

      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', 'advisor_notes.csv');
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    }
  },
  mounted() {
    this.fetchNotes();
    this.fetchStudents();
  }
};
</script>










