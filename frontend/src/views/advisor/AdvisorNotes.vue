<template>
  <div class="p-6 max-w-5xl mx-auto">
    <h2 class="text-2xl font-bold mb-4">🗒️ Advisor Meeting Notes</h2>

    <form @submit.prevent="submitNote" class="bg-gray-50 p-4 rounded mb-6 border">
      <h3 class="font-semibold mb-2">Add New Note</h3>

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
        Meeting Date:
        <input type="date" v-model="form.meeting_date" class="w-full border rounded p-2 mt-1" required />
      </label>

      <label class="block mb-4">
        Note:
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

    <table class="w-full border rounded" v-if="notes.length">
      <thead class="bg-gray-200">
        <tr>
          <th class="px-3 py-2">Student</th>
          <th class="px-3 py-2">Matric No</th>
          <th class="px-3 py-2">Meeting Date</th>
          <th class="px-3 py-2">Note</th>
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

    <p v-else class="text-gray-500 mt-4">No notes available yet.</p>
  </div>
</template>

<script>
import api from '@/api';

export default {
  name: 'AdvisorNotes',
  data() {
    return {
      notes: [],
      students: [],
      loading: false,
      form: {
        student_id: '',
        meeting_date: '',
        note: ''
      }
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

