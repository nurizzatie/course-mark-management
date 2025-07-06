<template>
  <AppLayout :role="'Advisor'" :navItems="navItems" :pageTitle="'Advisor Notes'">
    <div class="p-6 max-w-5xl mx-auto">
      <h2 class="text-2xl font-bold mb-4">🗒️ Add Consultation Report Session</h2>

      <!-- Download Button -->
      <div class="mb-4 text-right">
        <button
          class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded"
          @click="downloadNotes"
        >
          ⬇️ Download Report
        </button>
      </div>

      <!-- Add Note Form -->
      <form @submit.prevent="submitNote" class="bg-gray-50 p-4 rounded mb-6 border">
        <h3 class="font-semibold mb-2">Remark</h3>

        <label class="block mb-2">
          Student:
          <select v-model="form.student_id" class="w-full border rounded p-2 mt-1" required>
            <option disabled value="">Select student</option>
            <option v-for="student in students" :key="student.id" :value="student.id">
              {{ student.name }} ({{ student.matric_number }})
            </option>
          </select>
        </label>

        <label class="block mb-2">
          Consultation Date:
          <input type="date" v-model="form.meeting_date" class="w-full border rounded p-2 mt-1" required />
        </label>

        <label class="block mb-4">
          Remark:
          <textarea v-model="form.note" class="w-full border rounded p-2 mt-1" required></textarea>
        </label>

        <button
          type="submit"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
          :disabled="loading"
        >
          {{ loading ? 'Saving...' : 'Add Note' }}
        </button>
      </form>

      <!-- Notes Table -->
      <table class="w-full border rounded" v-if="notes.length">
        <thead class="bg-gray-200">
          <tr>
            <th class="px-3 py-2">Student</th>
            <th class="px-3 py-2">Matric No</th>
            <th class="px-3 py-2">Meeting Date</th>
            <th class="px-3 py-2">Remark</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="note in notes" :key="note.id" class="border-t">
            <td class="px-3 py-2">{{ note.student_name }}</td>
            <td class="px-3 py-2">{{ note.matric_number }}</td>
            <td class="px-3 py-2">{{ note.meeting_date }}</td>
            <td class="px-3 py-2">{{ note.note }}</td>
          </tr>
        </tbody>
      </table>

      <p v-else class="text-gray-500 mt-4">No details yet.</p>
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
        { name: 'Student List', link: '/advisor/students' },
        { name: 'Review Marks', link: '/advisor/reviews' },
        { name: 'Performance Analytics', link: '/advisor/analytics' },
        { name: 'High-Risk Students', link: '/advisor/high-risk-students' },
        { name: 'Advisor Notes', link: '/advisor/notes' },
        { name: 'Profile', link: '/advisor/profile' }
      ]
    };
  },
  methods: {
    async fetchNotes() {
      try {
        const advisor = JSON.parse(localStorage.getItem('user'));
        if (!advisor?.id) throw new Error('Missing advisor info in localStorage');

        const res = await api.get(`/advisor/notes?advisor_id=${advisor.id}`);
        this.notes = res.data;
      } catch (err) {
        console.error('Failed to fetch notes:', err);
        this.notes = [];
      }
    },

    async fetchStudents() {
      try {
        const res = await api.get('/advisor/students');
        this.students = res.data;
      } catch (err) {
        console.error('Failed to fetch students:', err);
        this.students = [];
      }
    },

    async submitNote() {
      try {
        this.loading = true;
        const advisor = JSON.parse(localStorage.getItem('user'));
        if (!advisor?.id) throw new Error('Missing advisor info in localStorage');

        const payload = {
          advisor_id: advisor.id,
          student_id: this.form.student_id,
          meeting_date: this.form.meeting_date,
          note: this.form.note
        };

        await api.post('/advisor/notes', payload);
        this.form = { student_id: '', meeting_date: '', note: '' };
        await this.fetchNotes();
        alert('Note added successfully!');
      } catch (err) {
        console.error('Failed to submit note:', err);
        alert('Something went wrong while adding note.');
      } finally {
        this.loading = false;
      }
    },

    downloadNotes() {
      if (!this.notes.length) {
        alert("No notes to download.");
        return;
      }

      const csvHeader = [
        "Student Name",
        "Matric Number",
        "Meeting Date",
        "Note"
      ];

      const csvRows = this.notes.map(note => [
        note.student_name,
        note.matric_number,
        note.meeting_date,
        `"${note.note.replace(/"/g, '""')}"`
      ]);

      const csvContent = [
        csvHeader.join(","),
        ...csvRows.map(row => row.join(","))
      ].join("\n");

      const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.setAttribute("download", "advisor_notes.csv");
      link.click();
    }
  },
  mounted() {
    this.fetchNotes();
    this.fetchStudents();
  }
};
</script>

<style scoped>
th {
  text-align: left;
}
</style>




