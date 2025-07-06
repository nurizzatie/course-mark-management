<template>
  <div class="container py-4">
    <!-- Title & Search -->
    <div class="mb-4">
      <h5 class="fw-bold mb-3">📝 Student Assessment Marks</h5>
      <input
        type="text"
        class="form-control"
        placeholder="🔍 Search by Matric No or Student Name..."
        v-model="searchQuery"
      />
    </div>

    <!-- Marks Table -->
    <div class="card shadow-sm">
      <div class="card-header bg-dark text-white fw-bold">Assessment Marks</div>
      <div class="card-body table-responsive">
        <table class="table table-bordered table-hover align-middle text-center">
          <thead class="table-dark">
            <tr>
              <th>Student Name</th>
              <th>Matric No</th>
              <th>Assessment</th>
              <th>Mark</th>
              <th>Max Mark</th>
              <th>Last Updated</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!filteredReviews.length">
              <td colspan="6" class="text-muted text-center py-3">No matching results.</td>
            </tr>
            <tr
              v-for="review in filteredReviews"
              :key="`${review.student_id}-${review.assessment_name}`"
            >
              <td>{{ review.student_name }}</td>
              <td>{{ review.matric_number }}</td>
              <td>{{ review.assessment_name }}</td>
              <td>{{ review.obtained_mark }}</td>
              <td>{{ review.max_mark }}</td>
              <td class="text-muted">{{ formatDate(review.updated_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>



<script>
import api from '@/api';

export default {
  name: 'ReviewMark',
  data() {
    return {
      reviews: [],
      searchQuery: ''
    };
  },
  computed: {
    filteredReviews() {
      const query = this.searchQuery.trim().toLowerCase();
      return this.reviews.filter(r =>
        r.matric_number.toLowerCase().includes(query) ||
        r.student_name.toLowerCase().includes(query)
      );
    }
  },
  methods: {
    formatDate(datetime) {
      return new Date(datetime).toLocaleString();
    },
    async fetchReviews() {
      try {
        const res = await api.get('/advisor/marks');
        this.reviews = res.data;
      } catch (err) {
        console.error('Failed to load marks:', err);
      }
    }
  },
  mounted() {
    this.fetchReviews();
  }
};
</script>




