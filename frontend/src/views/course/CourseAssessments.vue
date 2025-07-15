<template>
  <div class="assessments-page">
    <h2>Assessments</h2>

    <div class="tabs">
      <button
        v-for="t in types"
        :key="t"
        :class="{ active: selectedType === t }"
        @click="selectType(t)"
      >
        {{ t }}
      </button>
    </div>

    <div v-if="loading">Loading...</div>
    <div v-else>
      <table v-if="filtered.length">
        <thead>
          <tr>
            <th>Title</th>
            <th>Due Date</th>
            <th>Marks</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in filtered" :key="a.id">
            <td>{{ a.title }}</td>
            <td>{{ formatDate(a.due_date) }}</td>
            <td>{{ a.total_marks }}</td>
            <td>{{ a.status }}</td>
          </tr>
        </tbody>
      </table>
      <p v-else>No {{ selectedType }} found.</p>
    </div>
  </div>
</template>

<script>
import api from '@/api';

export default {
  name: 'CourseAssessments',
  data() {
    return {
      types: ['All', 'Assignment', 'Quiz', 'Test'],
      selectedType: 'All',
      assessments: [],
      loading: true
    };
  },
  computed: {
    filtered() {
      if (this.selectedType === 'All') return this.assessments;
      return this.assessments.filter(
        a => a.type.toLowerCase() === this.selectedType.toLowerCase()
      );
    }
  },
  methods: {
    async fetchAssessments() {
      this.loading = true;
      const courseId = this.$route.params.courseId;
      try {
        const res = await api.get(`/courses/${courseId}/assessments`);
        this.assessments = res.data.assessments;
      } catch (err) {
        console.error('Failed to fetch assessments:', err);
      } finally {
        this.loading = false;
      }
    },
    selectType(type) {
      this.selectedType = type;
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString();
    }
  },
  mounted() {
    this.fetchAssessments();
  }
};
</script>

<style scoped>
.assessments-page {
  padding: 20px;
}

.tabs {
  margin-bottom: 20px;
}

.tabs button {
  margin-right: 10px;
  padding: 8px 16px;
  border: none;
  background: #eee;
  cursor: pointer;
}

.tabs button.active {
  background: #5d001d;
  color: white;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 10px;
  border: 1px solid #ccc;
}
</style>
